<?php

namespace app\widgets;

use yii\base\Widget;
use yii\helpers\Html;

class Modal extends Widget
{
  public $id;
  public $size;
  public $close;
  public $title;
  public $footer;

  public function init()
  {
    parent::init();
    if ($this->id === null) {
      $this->id = 'modal';
    }
  }

  public function run()
  {
    $script = <<<JS
      // 1. Use a namespaced click handler to prevent multiple attachments
      $('body').off('click.modalToggle').on('click.modalToggle', "[data-bs-toggle='modal']", function() {
          var target = $(this).data('bs-target');
          var url = $(this).data('url');
          var title = $(this).data('title');
          if (target === '#{$this->id}') {
              $('#{$this->id}-content').load(url);
              $('#{$this->id}Label').text(title);
          }
      });

      var formChanged = false;
      $(document).off('change.modalForm').on('change.modalForm', ".modal form :input", function() {
          formChanged = true;
      });

      // Reset state when modal starts to hide
      $('#{$this->id}').on("hide.bs.modal", function () {
          formChanged = false;
      });

      // Fix: Force remove backdrops if they get stuck
      $('#{$this->id}').on("hidden.bs.modal", function () {
          $('.modal-backdrop').remove();
          $('body').css('overflow', '');
          $('body').removeClass('modal-open');
          $("#{$this->id}-content").html("");
      });

      $('body').off('click.modalDismiss').on("click.modalDismiss", "#{$this->id} #btn-dismiss-modal", function (e) {
        e.preventDefault();
        if (formChanged) {
          Swal.fire({
              title: 'Are you sure?',
              text: "Unsaved changes will be lost.",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Yes, close it!'
          }).then((result) => {
              if (result.isConfirmed) {
                var modalInstance = bootstrap.Modal.getInstance(document.getElementById('{$this->id}'));
                modalInstance.hide();
              }
          });
        } else {
          var modalInstance = bootstrap.Modal.getInstance(document.getElementById('{$this->id}'));
          modalInstance.hide();
        }
      });
    JS;
    $this->getView()->registerJs($script, \yii\web\View::POS_END);
    return $this->renderModal();
  }

  protected function renderModal()
  {
    $dataBackdrop = empty($this->close) ? 'static' : 'auto';
    $dataKeyboard = empty($this->close) ? 'false' : 'true';
    $modal = Html::beginTag('div', [
      'class' => 'modal fade',
      'id' => $this->id,
      'tabindex' => '-1',
      'aria-labelledby' => $this->id . 'Label',
      'aria-hidden' => 'true',
      'data-bs-backdrop' => $dataBackdrop,
      'data-bs-keyboard' => $dataKeyboard,
    ]);
    $modal .= Html::beginTag('div', ['class' => "modal-dialog {$this->size}"]);
    $modal .= Html::beginTag('div', ['class' => 'modal-content']);

    // Modal Header
    $modal .= Html::beginTag('div', ['class' => 'modal-header']);
    $modal .= Html::tag('h5', $this->title, [
      'class' => 'modal-title',
      'id' => $this->id . 'Label',
    ]);
    $modal .= Html::endTag('div');

    // Modal Body
    $modal .= Html::beginTag('div', ['class' => 'modal-body']);
    $modal .= Html::tag('div', '', ['id' => $this->id . '-content']);
    $modal .= Html::endTag('div');

    // Modal Footer
    if ($this->footer !== null) {
      $modal .= Html::beginTag('div', ['class' => 'modal-footer']);
      $modal .= $this->footer;
      $modal .= Html::endTag('div');
    }

    $modal .= Html::endTag('div');
    $modal .= Html::endTag('div');
    $modal .= Html::endTag('div');

    return $modal;
  }
}
