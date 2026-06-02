<?php
/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $modelVariation app\models\ProductVariation */
/* @var $form yii\widgets\ActiveForm */
/* @var $sources array */
/* @var $warranties array */
/* @var $descriptionSourceVariationId int|null */

use app\models\website\ProductDescriptionType;
use app\models\website\ProductDescription;
use yii\helpers\Html;

$descriptionTypes = ProductDescriptionType::find()
  ->select(['id', 'display_name', 'name'])
  ->where(['status' => 1])
  ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
  ->asArray()
  ->all();

$typeMap = [];
foreach ($descriptionTypes as $row) {
  $id = isset($row['id']) ? (int) $row['id'] : 0;
  $label = trim((string) ($row['display_name'] ?: $row['name']));
  if ($id > 0 && $label !== '') {
    $typeMap[$id] = $label;
  }
}

$postedDescriptions = Yii::$app->request->post('description', []);
$postedTypeIds = isset($postedDescriptions['type_id']) && is_array($postedDescriptions['type_id'])
  ? $postedDescriptions['type_id']
  : [];
$postedValues = isset($postedDescriptions['value']) && is_array($postedDescriptions['value'])
  ? $postedDescriptions['value']
  : [];

if (empty($postedTypeIds) && empty($postedValues)) {
  $variationIdForDescription = null;

  if (!$modelVariation->isNewRecord) {
    $variationIdForDescription = (int) $modelVariation->id;
  } elseif (!empty($descriptionSourceVariationId)) {
    $variationIdForDescription = (int) $descriptionSourceVariationId;
  }

  if ($variationIdForDescription) {
  $existingDescriptions = ProductDescription::find()
    ->where(['variation_id' => $variationIdForDescription])
    ->andWhere(['or', ['status' => null], ['<>', 'status', 10]])
    ->orderBy(['id' => SORT_ASC])
    ->all();

  foreach ($existingDescriptions as $existingDescription) {
    $postedTypeIds[] = (int) $existingDescription->type_id;
    $postedValues[] = (string) $existingDescription->description;
  }
  }
}
?>

<div class="row">
  <div class="col-lg-4">
    <h6>Photo</h6>
    <div class="row">
      <div class="col-md-12">
        <?= $form
          ->field($modelVariation, 'imageFile')
          ->widget(\app\widgets\ImageUploadWidget::class, [
            'imageUrl' =>
              $modelVariation->isNewRecord || empty($modelVariation->getImagePath())
                ? null
                : $modelVariation->getImagePath(),
          ])
          ->label(false) ?>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <?= $form->field($modelVariation, 'name')->textInput(['maxlength' => true]) ?>
    <div class="row">
      <div class="col-lg-6">
       <?= $form->field($modelVariation, 'warranty_id')->dropDownList($warranties, [
        'class' => 'has-select2',
        'prompt' => 'Select Warranty',
        ]) ?>
      </div>
      <div class="col-lg-6">
       <?= $form->field($modelVariation, 'source_id')->dropDownList($sources, [
        'class' => 'has-select2',
        'prompt' => 'Select Source',
        ]) ?>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-6">
        <?= $form->field($modelVariation, 'price')->textInput(['type' => 'number', 'step' => '0.01']) ?>
      </div>
    </div>
  </div>
</div>
<div class="product-description">
  <div class="row g-3 align-items-end mb-3">
    <div class="col-lg-7">
      <h6>Description</h6>
    </div>
    <div class="col-lg-5">
      <label class="form-label fw-semibold">Add Item</label>
      <select class="form-control" id="description-item-selector">
        <option value="">Select</option>
        <?php foreach ($typeMap as $typeId => $typeLabel): ?>
          <option value="<?= (int) $typeId ?>"><?= Html::encode($typeLabel) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div id="description-items-wrapper" class="description-items-wrapper">
    <?php foreach ($postedTypeIds as $index => $typeId): ?>
      <?php
      $typeId = (int) $typeId;
      if ($typeId < 1 || !isset($typeMap[$typeId])) {
        continue;
      }
      $value = isset($postedValues[$index]) ? (string) $postedValues[$index] : '';
      ?>
      <div class="description-item-row" data-type-id="<?= $typeId ?>">
        <div class="description-item-label"><?= Html::encode($typeMap[$typeId]) ?></div>
        <div class="description-item-input">
          <input type="hidden" name="description[type_id][]" value="<?= $typeId ?>">
          <input type="text" name="description[value][]" class="form-control" maxlength="50" value="<?= Html::encode($value) ?>">
        </div>
        <div class="description-item-actions">
          <button type="button" class="btn btn-sm btn-outline-danger description-action-btn remove-description-item" title="Remove">
            <i class="ri-delete-bin-line"></i>
          </button>
          <button type="button" class="btn btn-sm btn-outline-primary description-action-icon move-description-up" title="Move Up">
            <i class="ri-arrow-up-line"></i>
          </button>
          <button type="button" class="btn btn-sm btn-outline-primary description-action-icon move-description-down" title="Move Down">
            <i class="ri-arrow-down-line"></i>
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?= $form->field($modelVariation, 'description')->textArea(['rows' => 5]) ?>
</div>

<?php
$descriptionTypeJson = json_encode($typeMap);

$js = <<<JS
(function () {
  var typeMap = $descriptionTypeJson || {};
  var selector = document.getElementById('description-item-selector');
  var wrapper = document.getElementById('description-items-wrapper');

  if (!selector || !wrapper) {
    return;
  }

  function updateOptionAvailability() {
    var selected = {};
    wrapper.querySelectorAll('.description-item-row').forEach(function (row) {
      selected[row.dataset.typeId] = true;
    });

    Array.prototype.slice.call(selector.options).forEach(function (option) {
      if (!option.value) {
        return;
      }
      option.disabled = !!selected[option.value];
    });

  }

  function renderRow(typeId, value) {
    var label = typeMap[typeId] || '';
    if (!label) {
      return;
    }

    var row = document.createElement('div');
    row.className = 'description-item-row';
    row.dataset.typeId = typeId;
    row.innerHTML =
      '<div class="description-item-label"></div>' +
      '<div class="description-item-input">' +
      '<input type="hidden" name="description[type_id][]">' +
      '<input type="text" name="description[value][]" class="form-control" maxlength="50">' +
      '</div>' +
      '<div class="description-item-actions">' +
      '<button type="button" class="btn btn-sm btn-outline-danger description-action-btn remove-description-item" title="Remove"><i class="ri-delete-bin-line"></i></button>' +
      '<button type="button" class="btn btn-sm btn-outline-primary description-action-icon move-description-up" title="Move Up"><i class="ri-arrow-up-line"></i></button>' +
      '<button type="button" class="btn btn-sm btn-outline-primary description-action-icon move-description-down" title="Move Down"><i class="ri-arrow-down-line"></i></button>' +
      '</div>';

    row.querySelector('.description-item-label').textContent = label;
    row.querySelector('input[name="description[type_id][]"]').value = typeId;
    row.querySelector('input[name="description[value][]"]').value = value || '';

    wrapper.appendChild(row);
  }

  function handleAddItem() {
    var typeId = selector.value;
    if (!typeId || wrapper.querySelector('.description-item-row[data-type-id="' + typeId + '"]')) {
      return;
    }

    renderRow(typeId, '');
    selector.value = '';
    updateOptionAvailability();
  }

  selector.addEventListener('change', handleAddItem);
  selector.addEventListener('input', handleAddItem);

  wrapper.addEventListener('click', function (event) {
    var target = event.target.closest('button');
    if (!target) {
      return;
    }

    var row = target.closest('.description-item-row');
    if (!row) {
      return;
    }

    if (target.classList.contains('remove-description-item')) {
      row.remove();
      updateOptionAvailability();
      return;
    }

    if (target.classList.contains('move-description-up') && row.previousElementSibling) {
      wrapper.insertBefore(row, row.previousElementSibling);
      return;
    }

    if (target.classList.contains('move-description-down') && row.nextElementSibling) {
      wrapper.insertBefore(row.nextElementSibling, row);
    }
  });

  updateOptionAvailability();
})();
JS;

$css = <<<CSS
.product-description {
  margin-top: 1.5rem;
  padding: 1.25rem;
  border: 1px solid #e3e5ea;
  border-radius: 12px;
  background: #fff;
}

.product-description-title {
  font-size: 2rem;
  font-weight: 700;
  color: #353847;
}

.description-items-wrapper {
  border-top: 1px solid #d7dae0;
  padding-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}

.description-item-row {
  display: grid;
  grid-template-columns: minmax(120px, 200px) minmax(220px, 1fr) auto;
  align-items: center;
  gap: 1rem;
}

.description-item-label {
  font-size: .8rem;
  font-weight: 500;
  color: #3a3d4c;
}

.description-item-actions {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}


@media (max-width: 991.98px) {
  .description-item-row {
    grid-template-columns: 1fr;
    gap: 0.6rem;
  }
}
CSS;

$this->registerCss($css);
$this->registerJs($js);
?>