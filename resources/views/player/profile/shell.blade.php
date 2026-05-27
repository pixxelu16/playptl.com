@extends('player.profile.layout')

@section('profile_title', $profilePageTitle)
@section('profile_meta_description', $profileMetaDescription)

@section('profile_panel')
    @include('player.profile.panels.'.$activeSection)
@endsection
