<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $fileupload;

    public function rules()
	{
        return [
            [['fileupload'], 'file', 'skipOnEmpty' => false, 'extensions' => 'csv'],
        ];
	}
    
    public function attributeLabels()
	{
		return array(
			'file' => 'Select file',
		);
	}

    // public function upload()
    // {
    //     if ($this->validate()) {
    //         $this->fileupload->saveAs('uploads/' . $this->fileupload->baseName . '.' . $this->fileupload->extension);
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }
}

