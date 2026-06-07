<?php

use app\models\website\ProductBrand;
use app\models\website\ProductCategory;
use app\models\website\ProductModel;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\website\NavigationItem $model */

?>
<div class="frm-navigationItem">
  <?php

  $form = ActiveForm::begin([
    'id' => 'frm-navigationItem',
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
    'options' => ['enctype' => 'multipart/form-data'],
  ]);
  ?>

  <?= $form->field($model, 'name')->textInput(['class' => 'form-control', 'autofocus' => true]) ?>
  <?= $form->field($model, 'slug')->textInput(['class' => 'form-control']) ?>

  <?php
  if ($model->isNewRecord) {
    $model->category_id = [];
  } else {
    $model->category_id = array_values(array_unique(array_filter(ArrayHelper::getColumn($model->data, 'category_id'))));
  }
  echo $form->field($model, 'category_id')->dropDownList(
    ArrayHelper::map(ProductCategory::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
    [
      'class' => 'has-select2',
      'multiple' => true,
      'prompt' => 'Select',
    ],
  )->label('Select Category');
  ?>
  <?php
  if ($model->isNewRecord) {
    $model->brand_id = [];
  } else {
    $model->brand_id = array_values(array_unique(array_filter(ArrayHelper::getColumn($model->data, 'brand_id'))));
  }
  echo $form->field($model, 'brand_id')->dropDownList(
    ArrayHelper::map(ProductBrand::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
    [
      'class' => 'has-select2',
      'multiple' => true,
      'prompt' => 'Select',
    ],
  )->label('Select Brand');
  ?>

  <?php
  $productModelArray = [];
  if (!$model->isNewRecord) {
    // Load product model IDs from navigation_item_data in insertion order
    $navItemModelRows = \app\models\website\NavigationItemData::find()
      ->where(['nav_item_id' => $model->id])
      ->andWhere(['not', ['model_id' => null]])
      ->orderBy(['id' => SORT_ASC])
      ->all();

    foreach ($navItemModelRows as $row) {
      $modelId = (int)$row->model_id;
      if ($modelId > 0) {
        $productModelArray[] = $modelId;
      }
    }
  }
  
  // Store as string in model for hidden input
  $model->product_model_id = implode(',', $productModelArray);
  
  $allModels = ArrayHelper::map(ProductModel::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
  
  // Create hidden input to store the comma-separated values
  echo Html::activeHiddenInput($model, 'product_model_id');
  ?>
  
  <div class="mb-3">
    <label class="form-label">Sort by Product Model (drag to reorder)</label>
    <div id="sortable-product-models" class="border rounded p-3 mb-3 bg-light" style="min-height: 100px; display: flex; flex-direction: column; gap: 8px;"></div>
    <div class="mb-2">
      <select id="select-product-model" class="form-control has-select2">
        <option value="">Add Product Model...</option>
        <?php foreach ($allModels as $id => $name): ?>
          <option value="<?php echo $id; ?>"><?php echo Html::encode($name); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="d-flex justify-content-end gap-2 mt-3">
    <?= Html::button('Cancel', [
      'class' => 'btn btn-light',
      'id' => 'btn-dismiss-modal',
    ]) ?>
    <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
  </div>

  <?php ActiveForm::end(); ?>
</div>

<?php
// Register Sortable.js library - use POS_END to load after body
$this->registerJsFile('https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js', ['position' => \yii\web\View::POS_END]);

$js = <<<'JS'
// Wait for Sortable to be available
function initializeSortable() {
  if (typeof Sortable === 'undefined') {
    console.warn('Sortable.js not loaded yet, retrying...');
    setTimeout(initializeSortable, 100);
    return;
  }

  $(document).ready(function() {
    const selectedNames = {};
    let selectedOrder = [];
    let sortableInstance = null;

    // Get the hidden input - use attribute selector
    const hiddenInput = $('input[name="NavigationItem[product_model_id]"]');
    
    // Initialize selected order from hidden input (preserve sequence)
    const selectedValues = hiddenInput.val() ? hiddenInput.val().split(',').filter(v => v) : [];

    selectedValues.forEach(function(id) {
      const cleanId = String(id).trim();
      const option = $('select#select-product-model option[value="' + cleanId + '"]');
      if (option.length) {
        selectedNames[cleanId] = option.text();
        selectedOrder.push(cleanId);
      }
    });

    // Build model name lookup once
    $('#select-product-model option').each(function() {
      const val = $(this).val();
      if (val) {
        selectedNames[val] = selectedNames[val] || $(this).text();
      }
    });

    function syncOrderFromDom() {
      selectedOrder = [];
      $('#sortable-product-models [data-id]').each(function() {
        selectedOrder.push(String($(this).data('id')));
      });
      hiddenInput.val(selectedOrder.join(','));
    }

    function updateHiddenInput() {
      hiddenInput.val(selectedOrder.join(','));
    }

    // Render sortable list from ordered array (not object keys)
    function renderSortableList() {
      const container = $('#sortable-product-models');

      if (sortableInstance) {
        sortableInstance.destroy();
        sortableInstance = null;
      }

      container.empty();

      selectedOrder.forEach(function(id) {
        const item = $('<div class="badge bg-primary p-2 d-flex align-items-center justify-content-between" style="cursor: grab; user-select: none; gap: 8px; flex-wrap: nowrap;" data-id="' + id + '">')
          .append(
            $('<span>').text(selectedNames[id] || ('Model #' + id))
          )
          .append(
            $('<button type="button" class="btn-close btn-close-white ms-2" style="padding: 0;" aria-label="Remove"></button>')
              .on('click', function(e) {
                e.preventDefault();
                selectedOrder = selectedOrder.filter(function(v) { return v !== id; });
                updateHiddenInput();
                renderSortableList();
              })
          );
        container.append(item);
      });

      if (selectedOrder.length === 0) {
        container.append($('<div class="text-muted text-center py-4">No models selected. Select from dropdown to add.</div>'));
      }

      if (selectedOrder.length > 0) {
        const sortableContainer = document.getElementById('sortable-product-models');
        if (sortableContainer && typeof Sortable !== 'undefined') {
          sortableInstance = Sortable.create(sortableContainer, {
            animation: 150,
            ghostClass: 'opacity-50',
            onEnd: function() {
              syncOrderFromDom();
            }
          });
        }
      }

      updateHiddenInput();
    }

    // Handle select2 change
    $('#select-product-model').on('change', function() {
      const selectedId = String($(this).val() || '').trim();
      if (selectedId && selectedOrder.indexOf(selectedId) === -1) {
        selectedNames[selectedId] = $(this).find('option:selected').text() || selectedNames[selectedId] || ('Model #' + selectedId);
        selectedOrder.push(selectedId);
        renderSortableList();
        $(this).val(null).trigger('change');
      }
    });

    // Initial render
    renderSortableList();
  });
}

// Initialize when page is ready
initializeSortable();
JS;

$this->registerJs($js, \yii\web\View::POS_END);
?>
