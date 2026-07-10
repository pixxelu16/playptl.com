<section class="bg-[#E4F7E7] pb-6 pt-10 font-sans antialiased sm:pb-8 sm:pt-12" aria-labelledby="charity-goal-heading">
    <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
        <h2 id="charity-goal-heading" class="sr-only">Total donations raised</h2>
        <div class="relative overflow-hidden rounded-[15px] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.05] sm:p-8 lg:p-10">
            <div class="pointer-events-none absolute inset-y-0 right-0 z-0 w-[min(42%,280px)] opacity-95" aria-hidden="true">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_115%_95%_at_100%_50%,rgba(96,160,75,0.18)_0%,rgba(96,160,75,0.06)_38%,transparent_62%)]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_90%_78%_at_96%_48%,rgba(96,160,75,0.1)_0%,transparent_52%)]"></div>
            </div>

            <div class="relative z-[1] flex flex-col items-stretch gap-8 lg:flex-row lg:items-center lg:gap-10">
                <div class="min-w-0 shrink-0 text-left lg:max-w-[280px]">
                    <p class="text-[13px] font-medium leading-snug text-[#60a04b] sm:text-[14px]">Total Donations Raised</p>
                    <p
                        id="charity-total-raised"
                        class="mt-1.5 text-[clamp(1.75rem,4vw,2.35rem)] font-bold tabular-nums leading-tight tracking-tight text-[#212121]"
                    >
                        {{ $total_raised_formatted }}
                    </p>
                </div>

                <div class="min-w-0 flex-1 lg:mx-4 lg:max-w-none">
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-x-4 gap-y-1 text-[12px] font-normal leading-snug text-[#757575] sm:text-[13px]">
                        <span>$0</span>
                        <span id="charity-progress-label" class="font-medium text-[#212121]">{{ $progress_label }}</span>
                        <span id="charity-progress-scale-max">{{ $bar_scale_max_formatted }}</span>
                    </div>
                    <div
                        id="charity-progress-bar"
                        class="h-3 w-full overflow-hidden rounded-full bg-[#E8EDE8]"
                        role="progressbar"
                        aria-valuemin="0"
                        aria-valuemax="{{ $bar_scale_max }}"
                        aria-valuenow="{{ $total_raised }}"
                        aria-label="Donations raised"
                    >
                        <div
                            id="charity-progress-fill"
                            class="h-full rounded-full bg-[#60a04b] transition-[width] duration-500"
                            style="width: {{ $progress_percent }}%"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
