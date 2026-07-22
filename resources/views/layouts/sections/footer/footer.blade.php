@php
$containerFooter = (isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
    <div class="{{ $containerFooter }}">
        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
            <div class="text-body">
                &copy; {{ date('Y') }}, {{ config('app.name', 'GestoSecu') }} — Tous droits réservés
            </div>
        </div>
    </div>
</footer>
<!--/ Footer-->
