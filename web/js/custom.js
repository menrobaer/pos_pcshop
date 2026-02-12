$(function () {
  function initSelect2($root) {
    $root.find('.has-select2').not('.select2-hidden-accessible').select2();
  }

  // For non-modal elements
  initSelect2($(document));

  // For elements in a modal
  $(document).on('shown.bs.modal', '.modal', function () {
    $(this)
      .find('.has-select2')
      .select2({
        dropdownParent: $(this),
      });
  });

  // Re-init after PJAX updates
  $(document).on('pjax:success', function (event) {
    initSelect2($(event.target));
  });

  // SweetAlert2 Delete Confirmation
  $(document).on('click', '.sa-delete', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    var name = $(this).data('name') || 'this item';
    var pjaxContainer =
      $(this).data('pjax-container') ||
      $(this).closest('[data-pjax-container]').attr('id');

    // If still no pjaxContainer, try to find closest div with pjax in ID
    if (!pjaxContainer) {
      var closestPjax = $(this).closest('[id$="-pjax-container"]');
      pjaxContainer = closestPjax.length
        ? '#' + closestPjax.attr('id')
        : '#product-pjax-container';
    } else if (!pjaxContainer.startsWith('#')) {
      pjaxContainer = '#' + pjaxContainer;
    }

    Swal.fire({
      title: 'Are you sure?',
      text: 'You want to delete ' + name + '?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#f06548',
      cancelButtonColor: '#212529',
      confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: url,
          type: 'POST',
          data: {
            _csrf: yii.getCsrfToken(),
          },
          success: function (response) {
            if (response.success) {
              $.pjax.reload({ container: pjaxContainer });
              if (window.Toast) {
                window.Toast.fire({
                  icon: 'success',
                  title: response.message,
                });
              }
            } else {
              Swal.fire(
                'Error!',
                response.message || 'Failed to delete.',
                'error',
              );
            }
          },
          error: function () {
            Swal.fire('Error!', 'Something went wrong.', 'error');
          },
        });
      }
    });
  });

  // Auto-select text on focus/click for number inputs
  $(document).on('focus click', 'input[type="number"]', function () {
    $(this).select();
  });
});
