{{-- Scripts for every public website page (kept apart from the footer so footer edits can't drop them) --}}
<script src="{{ asset('js/jquery.js') }}"></script>
<script src="{{ asset('assets/web/js/jquery-migrate-3.5.2.min.js') }}"></script>
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('assets/web/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/web/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/web/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('assets/web/js/jquery.mmenu.all.js') }}"></script>
<script src="{{ asset('assets/web/js/ace-responsive-menu.js') }}"></script>
<script src="{{ asset('assets/web/js/jquery-scrolltofixed-min.js') }}"></script>
<script src="{{ asset('assets/web/js/wow.min.js') }}"></script>
<script src="{{ asset('assets/web/js/isotop.js') }}"></script>
<script src="{{ asset('assets/web/js/owl.js') }}"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/notifier.js') }}"></script>
<script src="{{ asset('assets/web/js/script.js') }}"></script>
<script src="{{ asset('assets/web/js/website.js') }}?v=3.7.8"></script>
@stack('script-page')

<script>
    var successImg = '{{ asset('assets/images/notification/ok-48.png') }}';
    var errorImg = '{{ asset('assets/images/notification/high_priority-48.png') }}';
</script>
<script src="{{ asset('js/custom.js') }}"></script>
@if ($statusMessage = Session::get('success'))
    <script>notifier.show(@json(__('Success!')), @json(strip_tags($statusMessage)), 'success', successImg, 4000);</script>
@endif
@if ($statusMessage = Session::get('error'))
    <script>notifier.show(@json(__('Error!')), @json(strip_tags($statusMessage)), 'error', errorImg, 4000);</script>
@endif
