<div
    class="min-h-[85vh] flex flex-col items-center justify-center px-lg mt-12 max-w-container-max mx-auto w-full animate-fade-in-up">

    <div class="relative w-64 h-64 flex items-center justify-center mb-xl">
        <div class="absolute inset-0 rounded-full border border-emerald-500/10 animate-ping duration-1000"></div>
        <div class="absolute inset-4 rounded-full border border-emerald-500/20"></div>
        <div class="absolute inset-12 rounded-full border border-slate-200 dark:border-slate-800"></div>

        <div class="absolute inset-0 rounded-full border border-emerald-500/30 overflow-hidden">
            <div
                class="w-1/2 h-1/2 bg-gradient-to-tr from-emerald-500/0 via-emerald-500/5 to-emerald-500/20 origin-bottom-right absolute top-0 left-0 animate-radar-spin">
            </div>
        </div>

        <div class="relative z-10 flex flex-col items-center">
            <h1
                class="text-7xl font-extrabold tracking-tighter text-slate-900 bg-gradient-to-r from-slate-900 via-emerald-500 to-slate-950 bg-clip-text text-transparent">
                404
            </h1>
            <span class="material-symbols-outlined text-emerald-500 text-3xl mt-2 animate-bounce">
                search_off
            </span>
        </div>
    </div>

    <div class="text-center max-w-md">
        <h2 class="font-headline-lg text-headline-lg text-slate-900 mb-sm font-bold">
            Spesimen Tidak Ditemukan
        </h2>
        <p class="font-body-md text-body-md text-on-surface-variant mb-xl leading-relaxed">
            Halaman atau data laboratorium yang Anda minta tidak terdaftar di dalam sistem radar logistik kami, atau
            sedang dalam pengembangan struktur database.
        </p>

        <a href="index.php?page=home"
            class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-medium px-6 py-3 rounded-xl shadow-lg transition-all duration-150 hover:-translate-y-0.5 active:translate-y-0">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali ke Dashboard
        </a>
    </div>
</div>

<style>

    @keyframes radar-spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(15px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-radar-spin {
        animation: radar-spin 4s linear infinite;
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>