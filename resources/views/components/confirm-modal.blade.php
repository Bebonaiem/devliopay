@once
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('confirm', {
            show: false,
            title: 'Confirm Action',
            message: 'Are you sure?',
            type: 'danger',
            confirmText: 'Confirm',
            callback: null,

            open(options) {
                this.title = options.title || 'Confirm Action';
                this.message = options.message || 'Are you sure?';
                this.type = options.type || 'danger';
                this.confirmText = options.confirmText || 'Confirm';
                this.callback = options.callback || null;
                this.show = true;
            },

            confirm() {
                this.show = false;
                if (typeof this.callback === 'function') {
                    this.callback();
                } else if (typeof this.callback === 'string') {
                    const form = document.getElementById(this.callback);
                    if (form) form.submit();
                }
            },

            cancel() {
                this.show = false;
            }
        });
    });

    function showConfirm(options) {
        Alpine.store('confirm').open(options);
    }
</script>
@endonce

<div x-data x-show="$store.confirm.show" x-cloak
    class="fixed inset-0 z-[200] flex items-center justify-center p-4"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="$store.confirm.cancel()"></div>
    <div class="relative w-full max-w-md glass rounded-2xl shadow-2xl border border-white/10 overflow-hidden"
        x-show="$store.confirm.show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4">
        <div class="px-6 pt-6 pb-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                    :class="$store.confirm.type === 'danger' ? 'bg-red-500/15' : $store.confirm.type === 'warning' ? 'bg-amber-500/15' : 'bg-brand-500/15'">
                    <svg x-show="$store.confirm.type === 'danger'" class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                    <svg x-show="$store.confirm.type === 'warning'" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                    <svg x-show="$store.confirm.type === 'info'" class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                </div>
                <h3 class="text-base font-semibold text-gray-100" x-text="$store.confirm.title"></h3>
            </div>
            <p class="text-sm text-gray-400 leading-relaxed" x-text="$store.confirm.message"></p>
        </div>
        <div class="px-6 pb-6 flex items-center justify-end gap-3">
            <button @click="$store.confirm.cancel()" class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-gray-200 bg-white/5 hover:bg-white/10 border border-white/10 transition-all">
                Cancel
            </button>
            <button @click="$store.confirm.confirm()" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white shadow-lg transition-all"
                :class="$store.confirm.type === 'danger' ? 'bg-red-500 hover:bg-red-400 shadow-red-500/20' : $store.confirm.type === 'warning' ? 'bg-amber-500 hover:bg-amber-400 shadow-amber-500/20' : 'btn-primary shadow-brand-500/20'">
                <span x-text="$store.confirm.confirmText"></span>
            </button>
        </div>
    </div>
</div>
