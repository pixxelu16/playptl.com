<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ProfileAvatarController extends Controller
{
    /**
     * Update the authenticated user's profile avatar.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'], // Max 8MB
        ]);

        if (!$request->hasFile('avatar')) {
            return response()->json(['message' => 'No image file uploaded.'], 400);
        }

        $file = $request->file('avatar');
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $filename = 'avatar-' . $user->id . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
        
        $dir = public_path('upload/user-avatar');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $targetPath = $dir . '/' . $filename;
        $sourcePath = $file->getRealPath();

        // Optimize and resize image using GD to a maximum of 400px
        $optimized = $this->optimizeAndResizeImage($sourcePath, $targetPath, 400);

        if (!$optimized) {
            // Fallback to standard move if GD fails or file format is unsupported by GD
            $file->move($dir, $filename);
        }

        // Clean up old avatar file
        $oldPath = (string) ($user->avatar_path ?? '');
        if ($oldPath !== '' && $oldPath !== 'upload/user-avatar/default-user-pic.png') {
            $oldFull = public_path($oldPath);
            if (File::exists($oldFull)) {
                File::delete($oldFull);
            }
        }

        $newPath = 'upload/user-avatar/' . $filename;
        $user->update(['avatar_path' => $newPath]);

        return response()->json([
            'message' => 'Profile photo updated successfully.',
            'avatar_url' => asset($newPath)
        ]);
    }

    /**
     * Scale and compress image using PHP GD to save storage and optimize load performance.
     */
    private function optimizeAndResizeImage($sourcePath, $targetPath, $maxSize)
    {
        $info = getimagesize($sourcePath);
        if (!$info) {
            return false;
        }

        $mime = $info['mime'];
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                if (!function_exists('imagecreatefromjpeg')) return false;
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                if (!function_exists('imagecreatefrompng')) return false;
                $image = imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                if (!function_exists('imagecreatefromwebp')) return false;
                $image = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        if (!$image) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= 0 || $height <= 0) {
            imagedestroy($image);
            return false;
        }

        // Determine new dimensions within the boundary box
        $aspectRatio = $width / $height;
        if ($width > $height) {
            $newWidth = $maxSize;
            $newHeight = (int) round($maxSize / $aspectRatio);
        } else {
            $newHeight = $maxSize;
            $newWidth = (int) round($maxSize * $aspectRatio);
        }

        // Avoid upscale if original is already smaller
        if ($width <= $maxSize && $height <= $maxSize) {
            $newWidth = $width;
            $newHeight = $height;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        if (!$newImage) {
            imagedestroy($image);
            return false;
        }

        // Retain alpha transparency for PNG and WebP formats
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            if ($transparent !== false) {
                imagefill($newImage, 0, 0, $transparent);
            }
        }

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save optimized image with compression
        $saved = false;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $saved = imagejpeg($newImage, $targetPath, 85); // 85% Quality
                break;
            case 'image/png':
                $saved = imagepng($newImage, $targetPath, 6); // Compression level 6 (0-9)
                break;
            case 'image/webp':
                $saved = imagewebp($newImage, $targetPath, 80); // 80% Quality
                break;
        }

        imagedestroy($image);
        imagedestroy($newImage);

        return $saved;
    }
}
