<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Interfaces;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Survey;

interface IRestoration extends IBaseItem
{
    public function setLangCode(string $langCode): void;

    public function getLangCode(): string;

    public function setInventoryNumberPrefix(string $inventoryNumberPrefix): void;

    public function getInventoryNumberPrefix(): string;

    public function setInventoryNumber(string $inventoryNumber): void;

    public function getInventoryNumber(): string;

    public function setObjectId(int $objectId): void;

    public function getObjectId(): int;

    public function addSurvey(Survey $survey);

    public function getSurveys(): array;
}
