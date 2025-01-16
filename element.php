<?

namespace metatagMigration;

use Bitrix\Iblock;

class Element
{
	public static $iblockId;
	public static $defaultNameFileExportForSection = 'exporting-elements-metatags.json';

	public function __construct(int|string $iblockId)
	{
		self::$iblockId = $iblockId;
	}

	// Метод обработки данных раздела для добавления в JSON файл
	public static function export()
	{
		if (empty(self::$iblockId)) throw new \Bitrix\Main\SystemException("Для работы необходимо в конструкторе класса определить id инфоблока");

		$iblockEntity = Iblock\Iblock::wakeUp(self::$iblockId)->getEntityDataClass();

		$objectElements = $iblockEntity::getList([
			'select' => ['ID', 'NAME', 'IBLOCK_ID', 'CODE'],
			'filter' => [
				'IBLOCK_ID' => self::$iblockId,
				'ACTIVE' => 'Y',
				//'SECTION_ACTIVE' => 'Y',
				//'ID' => [24706, 24704]
			],
			'order' => [],
		]);
		while ($arrayElements = $objectElements->Fetch()) {
			$elementFields['ID'] = $arrayElements['ID'];
			$elementFields['CODE'] = $arrayElements['CODE'];

			$sectionPropertyValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arrayElements['IBLOCK_ID'], $arrayElements['ID']);
			$queryValues = $sectionPropertyValues->queryValues();

			$elementFields['ELEMENT_META_TITLE']['CODE'] = $queryValues['ELEMENT_META_TITLE']['CODE'];
			$elementFields['ELEMENT_META_TITLE']['ENTITY_TYPE'] = $queryValues['ELEMENT_META_TITLE']['ENTITY_TYPE'];
			$elementFields['ELEMENT_META_TITLE']['TEMPLATE'] = $queryValues['ELEMENT_META_TITLE']['TEMPLATE'];
			$elementFields['ELEMENT_META_TITLE']['VALUE'] = $queryValues['ELEMENT_META_TITLE']['VALUE'];

			$elementFields['ELEMENT_META_DESCRIPTION']['CODE'] = $queryValues['ELEMENT_META_DESCRIPTION']['CODE'];
			$elementFields['ELEMENT_META_DESCRIPTION']['ENTITY_TYPE'] = $queryValues['ELEMENT_META_DESCRIPTION']['ENTITY_TYPE'];
			$elementFields['ELEMENT_META_DESCRIPTION']['TEMPLATE'] = $queryValues['ELEMENT_META_DESCRIPTION']['TEMPLATE'];
			$elementFields['ELEMENT_META_DESCRIPTION']['VALUE'] = $queryValues['ELEMENT_META_DESCRIPTION']['VALUE'];

			$finalArrayElements[$arrayElements['CODE']] = $elementFields;
		}

		Helper::createJsonFile(self::$defaultNameFileExportForSection);
		Helper::addDataJSONFile($finalArrayElements, self::$defaultNameFileExportForSection);
	}

	public static function import(string $fileName = '')
	{
		$fileName = (!empty($fileName) ? $fileName : self::$defaultNameFileExportForSection);
		$getElementsFieldsJson = file_get_contents($fileName);
		$elementsFields = json_decode($getElementsFieldsJson, true);
		$elementsCodes = [];
		foreach ($elementsFields as $keySection => $valueElement) {
			$elementsCodes[] = $valueElement['CODE'];
		}

		$iblockEntity = Iblock\Iblock::wakeUp(self::$iblockId)->getEntityDataClass();
		$objectElements = $iblockEntity::getList([
			'select' => ['ID', 'NAME', 'IBLOCK_ID', 'CODE'],
			'filter' => [
				'IBLOCK_ID' => self::$iblockId,
				'CODE' => $elementsCodes
			],
			'order' => [],
		]);

		while ($arrayElements = $objectElements->Fetch()) {
			$setFields = [];

			if ($elementsFields[$arrayElements['CODE']]["ELEMENT_META_TITLE"]["ENTITY_TYPE"] == 'E')
				$setFields['ELEMENT_META_TITLE'] = $elementsFields[$arrayElements['CODE']]["ELEMENT_META_TITLE"]["VALUE"];

			if ($elementsFields[$arrayElements['CODE']]["ELEMENT_META_DESCRIPTION"]["ENTITY_TYPE"] == 'E')
				$setFields['ELEMENT_META_DESCRIPTION'] = $elementsFields[$arrayElements['CODE']]["ELEMENT_META_DESCRIPTION"]["VALUE"];

			if (!empty($setFields)) {
				$sectionPropertyValues = new Iblock\InheritedProperty\ElementTemplates(self::$iblockId, $arrayElements["ID"]);
				$sectionPropertyValues->set($setFields);
			}
		}
	}
}
