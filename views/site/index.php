<?php

use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'My Yii Application';


?>

<div class="card">
    <form class="was-validated" enctype="multipart/form-data" method="post" action="<?= Url::to(['upload']) ?>">
        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>" />
        <div class="custom-file">
            <input type="file" name="file" class="custom-file-input" id="validatedCustomFile" onchange="form.submit()">
            <label class="custom-file-label" for="validatedCustomFile">Choose file...</label>
        </div>
    </form>
</div>

<hr />
<table id="example" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th>Keyword</th>
            <th>Total Links</th>
            <th>Total Results</th>
            <th>Search Time</th>
            <th>Last Update</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($keywords as $key => $value) { ?>
        <tr>
            <td><?=$value["keyword"]?></td>
            <td><?=$value["totalLinks"]?></td>
            <td><?=number_format($value["totalResults"])?></td>
            <td><?=$value["searchTime"]?></td>
            <td><?=$value["lastUpdate"]?></td>
        </tr>
   <?php }?>
    </tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0">
    <tbody>
        <tr>
            <td class="gutter">
                <div class="line number1 index0 alt2" style="display: none;">1</div>
            </td>
            <td class="code">
                <div class="container" style="display: none;">
                    <div class="line number1 index0 alt2" style="display: none;">&nbsp;</div>
                </div>
            </td>
        </tr>
    </tbody>
</table>