<div 
    x-data="toastNotification" 
    x-show="show" 
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed bottom-5 right-5 z-50 max-w-sm w-full bg-white rounded-2xl shadow-2xl border border-slate-100 p-4 flex items-start space-x-3 pointer-events-auto"
    style="display: none;">

    <template x-if="type === 'success'">
        <div class="p-2 rounded-xl bg-emerald-100 text-emerald-600">
            <i data-lucide="check-circle-2" class="w-5 h-5"></i>
        </div>
    </template>

    <template x-if="type === 'danger' || type === 'error'">
        <div class="p-2 rounded-xl bg-red-100 text-red-600">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
        </div>
    </template>

    <template x-if="type === 'info'">
        <div class="p-2 rounded-xl bg-blue-100 text-blue-600">
            <i data-lucide="info" class="w-5 h-5"></i>
        </div>
    </template>

    <div class="flex-1 min-w-0">
        <p class="text-xs font-bold text-slate-900" x-text="type.toUpperCase()"></p>
        <p class="text-xs text-slate-600 mt-0.5" x-text="message"></p>
    </div>

    <button @click="show = false" class="text-slate-400 hover:text-slate-600">
        <i data-lucide="x" class="w-4 h-4"></i>
    </button>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('toastNotification', () => ({
        show: false,
        message: '',
        type: 'info',
        init() {
            @if(session('toast'))
                this.notify({!! json_encode(session('toast.message')) !!}, {!! json_encode(session('toast.type', 'info')) !!});
            @endif
            window.addEventListener('toast', (e) => {
                this.notify(e.detail.message, e.detail.type);
            });
        },
        notify(msg, t = 'info') {
            this.message = msg;
            this.type = t;
            this.show = true;
            setTimeout(() => { this.show = false; }, 4000);
        }
    }));
});
</script>
