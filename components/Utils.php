<?php

namespace app\components;

use Yii;

class Utils
{
  public static function date($date, $format = 'd M, Y')
  {
    if (empty($date)) {
      return null;
    }
    return date($format, strtotime($date));
  }

  public static function dateTime($date, $format = 'd M, Y H:i:s')
  {
    if (empty($date)) {
      return null;
    }
    return date($format, strtotime($date));
  }

  public static function dollarFormat($amount)
  {
    if ($amount === null || $amount === '') {
      return null;
    }
    return '$' . number_format((float) $amount, 2);
  }

  /**
   * Insert an activity log entry.
   *
   * @param array $data
   * @return bool|\app\models\ActivityLog
   */
  public static function insertActivityLog(array $data = [])
  {
    if (!Yii::$app->request->isPost) {
      return false;
    }

    try {
      $log = new \app\models\ActivityLog();
      $log->user_id = $data['user_id'] ?? (Yii::$app->user->id ?? null);
      $log->controller =
        $data['controller'] ?? (Yii::$app->controller->id ?? null);
      $log->action =
        $data['action'] ?? (Yii::$app->controller->action->id ?? null);
      $log->method = $data['method'] ?? (Yii::$app->request->method ?? null);
      $params = $data['params'] ?? null;
      if (is_array($params)) {
        $log->params = !empty($params)
          ? json_encode($params, JSON_UNESCAPED_UNICODE)
          : null;
      } else {
        $log->params = $params ?? null;
      }
      $log->ip_address =
        $data['ip_address'] ?? (Yii::$app->request->userIP ?? null);
      $log->user_agent =
        $data['user_agent'] ?? (Yii::$app->request->userAgent ?? null);
      $log->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
      if ($log->save(false)) {
        return $log;
      }
      return false;
    } catch (\Throwable $e) {
      return false;
    }
  }

  /**
   * Record inventory in/out and update product available stock
   * @param int $productId Product ID
   * @param int $quantity Quantity to add/subtract
   * @param int $transactionId Transaction ID (PO ID, Invoice ID, etc.)
   * @param string $inventoryType Inventory type
   * @param string $direction 'in' or 'out' for inventory direction
   */
  public static function updateInventory(
    $productId,
    $quantity,
    $transactionId,
    $inventoryType,
    $direction = 'out',
  ) {
    try {
      $product = \app\models\Product::findOne($productId);
      if (!$product) {
        return;
      }

      // Create inventory record
      $inventory = new \app\models\Inventory();
      $inventory->product_id = $productId;
      $inventory->type = $inventoryType;
      $inventory->transaction_id = $transactionId;
      $inventory->created_at = date('Y-m-d H:i:s');
      $inventory->created_by = Yii::$app->user->id;

      if ($direction === 'in') {
        $inventory->in = $quantity;
        $inventory->out = 0;
        $product->available += $quantity;
      } else {
        $inventory->in = 0;
        $inventory->out = $quantity;
        $product->available -= $quantity;
      }

      $product->available = $product->available;
      $product->save(false);

      if (!$inventory->save(false)) {
        Yii::error(
          'Failed to save inventory record: ' . json_encode($inventory->errors),
          'inventory',
        );
      }
    } catch (\Throwable $e) {
      Yii::error('Inventory Update Error: ' . $e->getMessage(), 'inventory');
    }
  }
}
