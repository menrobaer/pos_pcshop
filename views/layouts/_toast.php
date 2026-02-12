<style>
  .colored-toast .swal2-title {
    color: #fff !important;
    padding: 0px;
    font-size: 16px;
  }
</style>

<?php
$this->registerJS('
window.Toast = Swal.mixin({
    toast: true,
    position: "bottom-end",
    showConfirmButton: false,
    timer: 5000,
    iconColor: "#fff",
    background: "#222230",
    width: "400px",
    customClass: {
        container: "colored-toast",
    }
});
');
$session = Yii::$app->session;
if ($session->hasFlash('success')) {
  $toast_text = $session->getFlash('success');
  $this->registerJS("
    window.Toast.fire({
        icon: 'success',
        title: \"$toast_text\"
    });
");
} elseif ($session->hasFlash('info')) {
  $toast_text = $session->getFlash('info');
  $this->registerJS("
    window.Toast.fire({
        icon: 'info',
        title: \"$toast_text\"
    });
");
} elseif ($session->hasFlash('warning')) {
  $toast_text = $session->getFlash('warning');
  $this->registerJS("
    window.Toast.fire({
        icon: 'warning',
        title: \"$toast_text\"
    });
");
} elseif ($session->hasFlash('error')) {
  $toast_text = $session->getFlash('error');
  $this->registerJS("
    window.Toast.fire({
        icon: 'error',
        title: \"$toast_text\"
    });
");
} elseif ($session->hasFlash('question')) {
  $toast_text = $session->getFlash('question');
  $this->registerJS("
    window.Toast.fire({
        icon: 'question',
        title: \"$toast_text\"
    });
");
}


?>
