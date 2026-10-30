@if(session('success') || session('error') || $errors->any())
    <div class="fixed top-[80px] ltr:right-[20px] rtl:left-[20px] z-[9999] w-full max-w-[380px] space-y-[10px]">
        @if(session('success'))
            <div class="glass-card flex items-start gap-[10px] border-l-4 !border-l-success-500 px-[16px] py-[13px] text-sm font-medium text-black dark:text-white">
                <i class="material-symbols-outlined !text-[20px] leading-none text-success-500">check_circle</i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="glass-card flex items-start gap-[10px] border-l-4 !border-l-danger-500 px-[16px] py-[13px] text-sm font-medium text-black dark:text-white">
                <i class="material-symbols-outlined !text-[20px] leading-none text-danger-500">error</i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="glass-card border-l-4 !border-l-danger-500 px-[16px] py-[13px] text-sm">
                <div class="mb-[6px] flex items-start gap-[10px] font-medium text-black dark:text-white">
                    <i class="material-symbols-outlined !text-[20px] leading-none text-danger-500">error</i>
                    <span>Periksa kembali data yang diisi.</span>
                </div>
                <ul class="list-disc text-gray-500 ltr:pl-[35px] rtl:pr-[35px] dark:text-gray-400">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
