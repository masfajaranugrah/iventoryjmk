<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @include('partials.front.styles')

        <title>Trezo - Tailwind CSS Admin Dashboard Template</title>

       @vite('resources/css/app.css')
    </head>
    <body>
        <!-- Light/Dark Mode Button -->
        <button type="button" class="light-dark-toggle leading-none inline-block transition-all text-[#fe7a36] absolute top-[20px] md:top-[25px] ltr:right-[20px] rtl:left-[20px] ltr:md:right-[25px] rtl:md:left-[25px]" id="light-dark-toggle">
            <i class="material-symbols-outlined !text-[20px] md:!text-[22px]">
                light_mode
            </i>
        </button>
        <!-- End Light/Dark Mode Button -->

        <!-- Sign In -->
        <div class="bg-white dark:bg-[#0a0e19] py-[60px] md:py-[80px] lg:py-[135px]">
            <div class="mx-auto px-[12.5px] md:max-w-[720px] lg:max-w-[960px] xl:max-w-[1255px]">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-[25px] items-center">
                    <div class="xl:ltr:-mr-[25px] xl:rtl:-ml-[25px] 2xl:ltr:-mr-[45px] 2xl:rtl:-ml-[45px] rounded-[25px] order-2 lg:order-1">
                        <img src="/assets/images/sign-in.jpg" alt="sign-in-image" class="rounded-[25px]">
                    </div>
                    <div class="xl:ltr:pl-[90px] xl:rtl:pr-[90px] 2xl:ltr:pl-[120px] 2xl:rtl:pr-[120px] order-1 lg:order-2">
                        <img src="/assets/images/logo-big.svg" alt="logo" class="inline-block dark:hidden">
                        <img src="/assets/images/white-logo-big.svg" alt="logo" class="hidden dark:inline-block">
                        <div class="my-[17px] md:my-[25px]">
                            <h1 class="!font-semibold !text-[22px] md:!text-xl lg:!text-2xl !mb-[5px] md:!mb-[7px]">
                                Login Inventori
                            </h1>
                            <p class="font-medium lg:text-md text-[#445164] dark:text-gray-400">
                                Masuk sebagai admin atau petugas.
                            </p>
                        </div>
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            @if ($errors->any())
                                <div class="mb-[15px] p-[12px] rounded-md bg-danger-50 dark:bg-[#15203c] text-danger-500 text-sm">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <div class="mb-[15px] relative">
                                <label class="mb-[10px] md:mb-[12px] text-black dark:text-white font-medium block">
                                    Email Address
                                </label>
                                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="h-[55px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[17px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500" placeholder="admin@inventori.test">
                            </div>
                            <div class="mb-[15px] relative" id="passwordHideShow">
                                <label class="mb-[10px] md:mb-[12px] text-black dark:text-white font-medium block">
                                    Password
                                </label>
                                <input type="password" name="password" required autocomplete="current-password" class="h-[55px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[17px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500" id="password" placeholder="Password">
                                <button class="absolute text-lg ltr:right-[20px] rtl:left-[20px] bottom-[12px] transition-all hover:text-primary-500" id="toggleButton" type="button">
                                    <i class="ri-eye-off-line"></i>
                                </button>
                            </div>
                            <label class="inline-flex items-center gap-[8px] text-sm text-black dark:text-white">
                                <input type="checkbox" name="remember" value="1" class="rounded border-gray-300">
                                Ingat saya
                            </label>
                            <button type="submit" class="md:text-md block w-full text-center transition-all rounded-md font-medium mt-[20px] md:mt-[25px] py-[12px] px-[25px] text-white bg-primary-500 hover:bg-primary-400">
                                <span class="flex items-center justify-center gap-[5px]">
                                    <i class="material-symbols-outlined">
                                        login
                                    </i>
                                    Sign In
                                </span>
                            </button>
                        </form>
                        <div class="mt-[20px] p-[12px] rounded-md bg-gray-50 dark:bg-[#15203c] text-sm text-gray-500 dark:text-gray-400">
                            <p class="mb-[4px]"><strong class="text-black dark:text-white">Admin:</strong> admin@inventori.test / password</p>
                            <p class="mb-0"><strong class="text-black dark:text-white">Petugas:</strong> petugas@inventori.test / password</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Sign In -->

        @include('partials.front.scripts')
    </body>
</html>
