<?

namespace metatagMigration;

use Bitrix\Iblock;

class Section
{
	public static $iblockId;
	public static $defaultNameFileExportForSection = 'exporting-sections-metatags.json';

	public function __construct(int|string $iblockId)
	{
		self::$iblockId = $iblockId;
	}

	// Метод обработки данных раздела для добавления в JSON файл
	public static function export()
	{

		if (empty(self::$iblockId)) throw new \Bitrix\Main\SystemException("Для работы необходимо в конструкторе класса определить id инфоблока");

		$entitySections = Iblock\Model\Section::compileEntityByIblock((int)self::$iblockId);
		$objectSections = $entitySections::getList([
			'select' => ['ID', 'NAME', 'IBLOCK_ID', 'CODE'],
			'filter' => [
				'IBLOCK_ID' => (int)self::$iblockId,
				'ACTIVE' => 'Y',
				'GLOBAL_ACTIVE' => 'Y',
				//'ID' => [7, 8]
			],
			'order' => [],
		]);

		while ($arraySections = $objectSections->Fetch()) {
			$sectionFields['ID'] = $arraySections['ID'];
			$sectionFields['CODE'] = $arraySections['CODE'];

			$sectionPropertyValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arraySections['IBLOCK_ID'], $arraySections['ID']);
			$queryValues = $sectionPropertyValues->queryValues();

			$sectionFields['SECTION_META_TITLE']['CODE'] = $queryValues['SECTION_META_TITLE']['CODE'];
			$sectionFields['SECTION_META_TITLE']['ENTITY_TYPE'] = $queryValues['SECTION_META_TITLE']['ENTITY_TYPE'];
			$sectionFields['SECTION_META_TITLE']['TEMPLATE'] = $queryValues['SECTION_META_TITLE']['TEMPLATE'];
			$sectionFields['SECTION_META_TITLE']['VALUE'] = $queryValues['SECTION_META_TITLE']['VALUE'];

			$sectionFields['SECTION_META_DESCRIPTION']['CODE'] = $queryValues['SECTION_META_DESCRIPTION']['CODE'];
			$sectionFields['SECTION_META_DESCRIPTION']['ENTITY_TYPE'] = $queryValues['SECTION_META_DESCRIPTION']['ENTITY_TYPE'];
			$sectionFields['SECTION_META_DESCRIPTION']['TEMPLATE'] = $queryValues['SECTION_META_DESCRIPTION']['TEMPLATE'];
			$sectionFields['SECTION_META_DESCRIPTION']['VALUE'] = $queryValues['SECTION_META_DESCRIPTION']['VALUE'];

			$sectionFields['ELEMENT_META_TITLE']['CODE'] = $queryValues['ELEMENT_META_TITLE']['CODE'];
			$sectionFields['ELEMENT_META_TITLE']['ENTITY_TYPE'] = $queryValues['ELEMENT_META_TITLE']['ENTITY_TYPE'];
			$sectionFields['ELEMENT_META_TITLE']['TEMPLATE'] = $queryValues['ELEMENT_META_TITLE']['TEMPLATE'];
			$sectionFields['ELEMENT_META_TITLE']['VALUE'] = $queryValues['ELEMENT_META_TITLE']['VALUE'];

			$sectionFields['ELEMENT_META_DESCRIPTION']['CODE'] = $queryValues['ELEMENT_META_DESCRIPTION']['CODE'];
			$sectionFields['ELEMENT_META_DESCRIPTION']['ENTITY_TYPE'] = $queryValues['ELEMENT_META_DESCRIPTION']['ENTITY_TYPE'];
			$sectionFields['ELEMENT_META_DESCRIPTION']['TEMPLATE'] = $queryValues['ELEMENT_META_DESCRIPTION']['TEMPLATE'];
			$sectionFields['ELEMENT_META_DESCRIPTION']['VALUE'] = $queryValues['ELEMENT_META_DESCRIPTION']['VALUE'];

			$finalArraySection[$sectionFields['CODE']] = $sectionFields;
		}

		Helper::createJsonFile(self::$defaultNameFileExportForSection);
		Helper::addDataJSONFile($finalArraySection, self::$defaultNameFileExportForSection);
	}

	public static function import(string $fileName = '')
	{
		$fileName = (!empty($fileName) ? $fileName : self::$defaultNameFileExportForSection);
		$getSectionFieldsJson = file_get_contents($fileName);
		$sectionFields = json_decode($getSectionFieldsJson, true);
		$sectionCodes = [];
		foreach ($sectionFields as $keySection => $valueSection) {
			$sectionCodes[] = $valueSection['CODE'];
		}

		$entitySections = Iblock\Model\Section::compileEntityByIblock((int)self::$iblockId);
		$objectSections = $entitySections::getList([
			'select' => ['ID', 'NAME', 'IBLOCK_ID', 'CODE'],
			'filter' => [
				'IBLOCK_ID' => (int)self::$iblockId,
				'CODE' => $sectionCodes
			],
			'order' => [],
		]);

		while ($arraySections = $objectSections->Fetch()) {
			$setFields = [];

			if ($sectionFields[$arraySections['CODE']]["SECTION_META_TITLE"]["ENTITY_TYPE"] == 'S')
				$setFields['SECTION_META_TITLE'] = $sectionFields[$arraySections['CODE']]["SECTION_META_TITLE"]["VALUE"];

			if ($sectionFields[$arraySections['CODE']]["SECTION_META_DESCRIPTION"]["ENTITY_TYPE"] == 'S')
				$setFields['SECTION_META_DESCRIPTION'] = $sectionFields[$arraySections['CODE']]["SECTION_META_DESCRIPTION"]["VALUE"];

			if ($sectionFields[$arraySections['CODE']]["ELEMENT_META_TITLE"]["ENTITY_TYPE"] == 'S')
				$setFields['ELEMENT_META_TITLE'] = $sectionFields[$arraySections['CODE']]["ELEMENT_META_TITLE"]["VALUE"];

			if ($sectionFields[$arraySections['CODE']]["ELEMENT_META_DESCRIPTION"]["ENTITY_TYPE"] == 'S')
				$setFields['ELEMENT_META_DESCRIPTION'] = $sectionFields[$arraySections['CODE']]["ELEMENT_META_DESCRIPTION"]["VALUE"];

			if (!empty($setFields)) {
				$sectionPropertyValues = new Iblock\InheritedProperty\SectionTemplates((int)self::$iblockId, $arraySections["ID"]);
				$sectionPropertyValues->set($setFields);
			}
		}
	}
}
