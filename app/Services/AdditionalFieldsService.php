<?php

namespace App\Services;

use App\Models\AdditionalField;

class AdditionalFieldsService
{
    public function sync($entity, $fields)
    {
        $currentFieldsKeys = [];
        $newFields = [];

        foreach ($fields as $key => $field) {
            if (isset($field['id']) && $field['id']) {
                $currentFieldsKeys[$field['id']] = $key;
            } else if ($field['name'] ?? null) {
                $newFields[] = $field;
            }
        }

        foreach ($entity->additionalFields as $field) {
            if (isset($currentFieldsKeys[$field->id])) { /* Обновляем существующие */
                $field->update($fields[$currentFieldsKeys[$field->id]]);
            } else { /* Удаляем не существующие */
                $field->delete();
            }
        }

        // TODO сохранить одним запросом?
        if ($newFields) {
            foreach ($newFields as $field) {
                $entity->additionalFields()->create($field);
            }
        }
    }
}
