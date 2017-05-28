<?php

class LotFinder extends BaseFinder
{
    /**
     * возвращает имя таблицы
     *
     * @return string имя таблицы
     */
    protected static function tableName()
    {
        return "lots";
    }

    /**
     * возвращает имя класса
     *
     * @return string имя класса
     */
    protected static function entityName()
    {
        return "Lot";
    }

    /**
     * Получение списка лотов для заданной категории
     *
     * @param integer $categoryId id категории по которой нужно найти лоты
     * @return Lot[] массив всех лотов для заданной категории
     */
    public static function getByCategoryId($categoryId, $offset = null)
    {
        $where = "end_date > NOW() and winner is NULL AND category_id = ?";
        $orderBy = "add_date DESC";
        return self::getAll($where, $orderBy, ITEMS_PER_PAGE, $offset, [$categoryId]);

    }

    /**
     *  получение всех открытых лотов
     *
     * @return Lot[] список всех лотов
     */
    public static function getAllOpened($offset = null)
    {
        $where = "end_date > NOW() and winner is NULL";
        $orderBy = "add_date DESC";
        return self::getAll($where, $orderBy, ITEMS_PER_PAGE, $offset);
    }

    /**
     * Возвращает лоты пользователя
     * @param $userId
     * @return Lot[]
     * @throws Exception
     */
    public static function getByUserId($userId)
    {
        return self::getByKey('user_id', $userId, 'add_date DESC', 9);
    }

    /**
     * осуществляет поиск по заданой строке
     *
     * @param string $searchString строка по которой осуществляется поиск
     * @return Lot[]
     */
    public static function searchByString($searchString, $offset = null)
    {
        $sql = "SELECT * FROM " . self::tableName();
        $where = "(end_date > NOW() and winner is NULL) and (name LIKE ? or description LIKE ?)";
        $orderBy = "add_date DESC";
        return self::getAll($where, $orderBy, ITEMS_PER_PAGE, $offset, ["%$searchString%", "%$searchString%"]);
    }

    /**
     * получение числа всех отркрытых лотов для категории или для всех категорий
     *
     * @param null $categoryId id категории
     * @return int количество записей
     */
    public static function getCountLots($categoryId = null)
    {
        $where = "end_date > NOW() and winner is NULL";
        if ($categoryId !== null) {
            $where .= " AND category_id=?";
            $data = [$categoryId];
        } else {
            $data = [];
        }
        return self::getAllCount($where, $data);
    }

    /**
     * Получение числа записей которые соответствуют искомой строке
     *
     * @param null $search строка для поиска
     * @return int количество записей
     */
    public static function getCountLotsForSearch($search = null)
    {
        $where = "(end_date > NOW() and winner is NULL)";
        if ($search !== null) {
            $where .= "and (name LIKE ? or description LIKE ?)";
            $data = ["%$search%", "%$search%"];
        } else {
            $data = [];
        }
        return self::getAllCount($where, $data);
    }




}