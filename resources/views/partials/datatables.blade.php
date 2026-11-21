{{-- Adds search, paging, page-length and sortable headers to every <table class="js-dt"> on the page. Include ONCE per page. --}}
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.11/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
  .dataTables_wrapper{padding:12px 14px}
  .dataTables_wrapper .dataTables_filter input{min-width:230px}
  table.js-dt th{white-space:nowrap}
</style>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.11/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
  $('table.js-dt').DataTable({
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    order: [],                                   // keep the order the server sent; click a header to sort
    autoWidth: false,
    columnDefs: [{ orderable: false, targets: -1 }],   // last column holds the buttons
    language: {
      search: '', searchPlaceholder: 'Search…', lengthMenu: 'Show _MENU_',
      emptyTable: 'Nothing here yet.', zeroRecords: 'No matching records found.',
      info: 'Showing _START_ to _END_ of _TOTAL_', infoEmpty: '', infoFiltered: '(filtered from _MAX_)',
    },
    drawCallback: function () {                  // hide the page buttons when everything fits on one page
      $(this).closest('.dataTables_wrapper').find('.dataTables_paginate').toggle(this.api().page.info().pages > 1);
    },
  });
  // Tables inside hidden tabs need their column widths recalculated when shown
  $(document).on('shown.bs.tab', function () { $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust(); });
});
</script>
@endpush
