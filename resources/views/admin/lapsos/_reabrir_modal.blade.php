{{-- Si el servidor rechazó los datos, vuelve a abrir el modal que los envió, con sus errores debajo de cada campo --}}
@if ($errors->any() && old('_modal'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById(@json(old('_modal')));
            if (modal && window.bootstrap) {
                new bootstrap.Modal(modal).show();
            }
        });
    </script>
@endif
