<?
namespace metatagMigration;

class Helper
{
	// Метод создания файла для экспорта
	public static function createJsonFile(string $fileName)
	{
		if (!file_exists($fileName)) file_put_contents($fileName, '');
	}

	// Метод добавления данных в JSON файл
	public static function addDataJSONFile(array $data, string $fileName)
	{
		if (!empty($data)) file_put_contents($fileName, json_encode($data));
	}
}
