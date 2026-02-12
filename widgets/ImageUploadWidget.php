<?php

namespace app\widgets;

use Yii;
use yii\widgets\InputWidget;
use yii\bootstrap5\Html;

class ImageUploadWidget extends InputWidget
{
  public $placeholder = 'Upload Image';
  public $imageUrl;

  public function init()
  {
    parent::init();
    if (!$this->imageUrl) {
      $this->imageUrl = Yii::getAlias('@web/images/upload_image_dummy.jpeg');
    }
  }

  public function run()
  {
    $previewId =
      strtolower($this->model->formName()) .
      "-{$this->attribute}" .
      '-image-preview';
    $this->options['onchange'] = 'showPreview(event,"' . $previewId . '");';
    $this->options['accept'] = 'image/*';

    $defaultImageUrl = Yii::getAlias('@web/images/upload_image_dummy.jpeg');
    $objectFit = $this->imageUrl == $defaultImageUrl ? 'cover' : 'contain';
    echo Html::beginTag('div', ['class' => 'form-image-upload']);
    echo Html::beginTag('div', ['class' => 'preview']);
    echo Html::img($this->imageUrl, [
      'id' => $previewId,
      'style' => "object-fit: $objectFit",
    ]);
    echo Html::label(
      "<i class='bi bi-plus'></i> " . $this->placeholder,
      strtolower($this->model->formName() . '-' . $this->attribute),
      [
        'data-target' => $previewId,
        'class' => 'btn btn-default btn-sm px-4',
        'style' => 'border: 1px dashed;',
        'data-toggle' => 'tooltip',
        'title' => 'CLick to upload an image',
      ],
    );

    $hasImage = $this->imageUrl && $this->imageUrl !== $defaultImageUrl;
    $removeBtnDisplay = $hasImage ? 'block' : 'none';

    // Always render the Remove Photo button, but hide it by default
    echo Html::button('Remove Photo', [
      'type' => 'button',
      'id' => $previewId . '-remove-btn',
      'class' => 'btn btn-danger btn-sm position-absolute',
      'style' => "top:10px; right:10px; z-index:2; display: $removeBtnDisplay;",
      'onclick' => 'removePhotoUploadWidget()',
    ]);
    echo Html::endTag('div');
    echo Html::activeFileInput($this->model, $this->attribute, $this->options);
    echo Html::endTag('div');

    $this->getView()->registerJs(
      <<<JS

          function showPreview(event,target) {
              var preview = document.getElementById(target);
              var removeBtn = document.getElementById(target + '-remove-btn');
              if (event.target.files.length > 0) {
                  var src = URL.createObjectURL(event.target.files[0]);
                  preview.src = src;
                  preview.style.display = "block";
                  preview.style.objectFit = "contain";
                  if (removeBtn) removeBtn.style.display = 'block';
              } else {
                  preview.src = "$defaultImageUrl";
                  preview.style.objectFit = "cover";
                  if (removeBtn) removeBtn.style.display = 'none';
              }
              preview.onload = function() {
                  URL.revokeObjectURL(preview.src)
              }
          }

          function removePhotoUploadWidget() {
              var preview = document.getElementById('{$previewId}');
              var input = document.getElementById('{$this->options['id']}');
              var removeBtn = document.getElementById('{$previewId}-remove-btn');
              preview.src = "$defaultImageUrl";
              preview.style.objectFit = "cover";
              input.value = '';
              if (removeBtn) removeBtn.style.display = 'none';
          }
      JS
      ,
      \yii\web\View::POS_END,
    );

    $this->getView()->registerCss(
      <<<CSS
      	.invalid-feedback {
      		display: block;
      	}
      	.form-image-upload {
      		width: 100%;
      		background: #fff;
      		border: 1px solid gainsboro;
      		border-radius: 8px;
      		/* box-shadow: -3px -3px 7px rgba(94, 104, 121, 0.377),
      		3px 3px 7px rgba(94, 104, 121, 0.377); */
      	}

      	.form-image-upload img {
      		width: 100%;
      		/* display: none; */
      		object-fit: contain;
      		height: 200px;
      		border-radius: 8px;
      	}

      	.form-image-upload input {
      		display: none;
      	}
      	.form-image-upload .preview {
      		position: relative;
      	}

      	.form-image-upload label {
      		position: absolute;
      		margin-bottom: 0;
      		bottom: 10px;
      		right: 10px;
      		z-index: 1;
      	}
      CSS
      ,
    );
  }
}
