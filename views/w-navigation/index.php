<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Navigation Setup';
$this->params['pageTitle'] = $this->title;
$this->params['breadcrumbs'][] = $this->title;

echo \app\widgets\Modal::widget([
  'id' => 'modal-navigation',
  'size' => 'modal-lg',
]);

$this->registerJsFile("https://code.jquery.com/ui/1.13.1/jquery-ui.min.js", ['depends' => [\yii\web\JqueryAsset::class]]);
?>
<style>
  .drop-placeholder {
    border: 1px dotted black;
    margin: 0;
    height: 50px;
  }
</style>
<div class="row">
  <div class="col-xl-6 col-lg-8">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-end">
          <?= Html::button('<i class="ri-add-line align-bottom me-1"></i> Add Main Menu', [
            'class' => 'btn btn-success',
            'data' => [
              'bs-toggle' => 'modal',
              'bs-target' => '#modal-navigation',
              'title' => 'Add Main Menu',
              'url' => Url::to(['create']),
            ],
          ]) ?>
        </div>
        <hr class="border-0">
        <div id="navigationAccordion" class="card-expansion sortable-parent-item">
          <?php
          if (!empty($navigation)) {
            foreach ($navigation as $key => $value) {
          ?>
              <div class="card card-expansion-item mb-3 parent-item cs-pointer" data-bs-toggle="tooltip" data-bs-title="Drag to order" data-previndex="<?= $value->sort ?>" data-id="<?= $value->id ?>">
                <div class="card-header border-1" id="navigation_<?= $key ?>">
                  <div class="d-flex justify-content-between">
                    <div><?= Html::encode($value->name) ?></div>
                    <div class="d-flex justify-content-end align-items-center gap-2">
                      <?= Html::a('<i class="ri-delete-bin-2-line"></i>', ['delete', 'id' => $value->id], [
                        'class' => 'btn btn-sm btn-outline-danger',
                        'data' => [
                          'bs-toggle' => 'tooltip',
                          'bs-title' => 'Delete this item',
                          'confirm' => 'Are you sure?',
                          'method' => 'post',
                        ],
                      ]); ?>
                      <?= Html::button('<i class="ri-pencil-line"></i>', [
                        'type' => 'button',
                        'class' => 'btn btn-sm btn-outline-primary',
                        'title' => 'Update Item',
                        'data' => [
                          'bs-toggle' => 'modal',
                          'bs-target' => '#modal-navigation',
                          'title' => 'Update Item',
                          'url' => Url::to(['update', 'id' => $value->id]),
                        ],
                      ]) ?>
                      <button type="button" class="btn btn-sm btn-outline-secondary collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_<?= $key ?>" aria-expanded="false" aria-controls="collapse_<?= $key ?>">
                        <span class="collapse-indicator"><i class="ri-arrow-down-s-line"></i></span>
                      </button>
                    </div>
                  </div>
                </div>
                <div id="collapse_<?= $key ?>" class="collapse" aria-labelledby="navigation_<?= $key ?>" data-bs-parent="#navigationAccordion">
                  <div class="card-body pt-0">
                    <div class="d-flex justify-content-end mb-3">
                      <?= Html::button('<i class="ri-add-line align-bottom me-1"></i> Add Item', [
                        'type' => 'button',
                        'class' => 'btn btn-link px-0',
                        'data' => [
                          'bs-toggle' => 'modal',
                          'bs-target' => '#modal-navigation',
                          'title' => 'Add Item',
                          'url' => Url::to(['add-item', 'parent' => $value->id]),
                        ],
                      ]) ?>
                    </div>
                    <ul class="list-group list-group-flush sortable-item mb-3">
                      <?php
                      if (!empty($value->item)) {
                        foreach ($value->item as $k => $v) {
                      ?>
                          <li data-bs-toggle="tooltip" data-bs-title="Drag to order" class="list-group-item d-flex justify-content-between align-items-center list_item cs-pointer" data-parent="<?= $value->id ?>" id="each_item_<?= $v->sort ?>" data-previndex="<?= $v->sort ?>" data-id="<?= $v->id ?>">
                            <span><?= Html::encode($v->name) ?></span>
                            <div class="d-flex align-items-center gap-2">
                              <?= Html::a('<i class="ri-delete-bin-2-line"></i>', ['delete-item', 'id' => $v->id], [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data' => [
                                  'bs-toggle' => 'tooltip',
                                  'bs-title' => 'Delete this item',
                                  'confirm' => 'Are you sure?',
                        'method' => 'post',
                                ],
                              ]); ?>
                              <?= Html::button('<i class="ri-pencil-line"></i>', [
                                'type' => 'button',
                                'class' => 'btn btn-sm btn-outline-primary',
                                'title' => 'Update Item',
                                'data' => [
                                  'bs-toggle' => 'modal',
                                  'bs-target' => '#modal-navigation',
                                  'title' => 'Update Item',
                                  'url' => Url::to(['update-item', 'id' => $v->id]),
                                ],
                              ]); ?>
                            </div>
                          </li>
                      <?php
                        }
                      }
                      ?>
                    </ul>
                  </div>
                </div>
              </div>
          <?php
            }
          }
          ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$dependentUrl = Url::to(['dependent']);

$script = <<<JS
  var ajax_url = '$dependentUrl';

  var Toast = Swal.mixin({
      toast: true,
      position: "top",
      showConfirmButton: false,
      timer: 5000,
      iconColor: "#fff",
      background: "#222230",
      customClass: {
          container: "colored-toast",
      }
  });

  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
    new bootstrap.Tooltip(element);
  });

  SortableRow();
  function SortableRow() {
    $(".sortable-item").sortable({
        placeholder: 'drop-placeholder',
        forceHelperSize: true,
        tolerance: 'pointer',
        start: function (e, ui) {
        },
        stop: function (e, ui) {
          var currentID = $(ui.item).data('id');
          var oldIndex = $(ui.item).data('previndex');
          var newIndex = ui.item.index();
          var parent =  $(ui.item).data('parent');
          newIndex += 1;

          var orderArr = [];
          $(".list_item[data-parent='"+parent+"']").each(function(){
              var active_id = $(this).data('id');
              orderArr.push(active_id);
          });    
          orderArr = jQuery.grep(orderArr, function(n, i){
              return (n !== "" && n != null);
          });
          
          $.ajax({
            url: ajax_url,
            type: 'post',
            data: {
                currentID: currentID,
                oldIndex: oldIndex,
                newIndex: newIndex,
                orderArr: orderArr,
                action: 'update_order'
            },
            success: function(response){
             var data = JSON.parse(response);
              if(data === 'saved'){
                Toast.fire({
                    icon: 'success',
                    title: 'Item sorted successful.'
                });
              }
            },
            error: function(response){
                console.log(response);
            }
          });

        }
    });
    $(".each_item").disableSelection();
  }

  SortableRowParent();
  function SortableRowParent() {
    $(".sortable-parent-item").sortable({
        placeholder: 'drop-placeholder',
        forceHelperSize: true,
        tolerance: 'pointer',
        start: function (e, ui) {
        },
        stop: function (e, ui) {
          var currentID = $(ui.item).data('id');
          var oldIndex = $(ui.item).data('previndex');
          var newIndex = ui.item.index();
          var parent =  $(ui.item).data('parent');
          newIndex += 1;

          var orderArr = [];
          $(".parent-item").each(function(){
              var active_id = $(this).data('id');
              orderArr.push(active_id);
          });    
          orderArr = jQuery.grep(orderArr, function(n, i){
              return (n !== "" && n != null);
          });
          
          $.ajax({
            url: ajax_url,
            type: 'post',
            data: {
                currentID: currentID,
                oldIndex: oldIndex,
                newIndex: newIndex,
                orderArr: orderArr,
                action: 'update_parent_order'
            },
            success: function(response){
             var data = JSON.parse(response);
              if(data === 'saved'){
                Toast.fire({
                    icon: 'success',
                    title: 'Item sorted successful.'
                });
              }
            },
            error: function(response){
                console.log(response);
            }
          });

        }
    });
  }

JS;
$this->registerJs($script);
?>