<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <h2 class="mt-6 text-center text-4xl font-serif font-bold text-gray-900 tracking-tighter uppercase">
        Login
    </h2>
    <p class="mt-4 text-center text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400">
        Amtliches Dokumentationssystem
    </p>
</div>

<div class="mt-12 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-gray-50 border-2 border-gray-900 p-8 sm:p-12 shadow-[12px_12px_0px_0px_rgba(0,0,0,0.05)]">
        <form class="space-y-8" action="<?= url('/login') ?>" method="POST">
            <div class="space-y-2">
                <label for="username" class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">
                    Benutzername
                </label>
                <input id="username" name="username" type="text" required class="form-input bg-white">
            </div>

            <div class="space-y-2">
                <label for="password" class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">
                    Passwort
                </label>
                <input id="password" name="password" type="password" required class="form-input bg-white">
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full flex justify-center btn btn-primary py-4 text-[10px] tracking-[0.2em]">
                    ANMELDEN
                </button>
            </div>
        </form>

        <div class="mt-8 pt-8 border-t border-gray-200 text-center">
            <a href="<?= url('/register') ?>" class="text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-gray-900 transition-colors">
                Neues Konto registrieren
            </a>
        </div>
    </div>
</div>
