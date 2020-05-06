<?php

namespace BristolSU\Module\UploadFile\Fields;

use FormSchema\Schema\Field;

class TagList extends Field
{

    protected $type = 'uploadFileTagList';
    
    public function getAppendedAttributes(): array
    {
        return [];
    }
}