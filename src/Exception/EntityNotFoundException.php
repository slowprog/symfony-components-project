<?php

namespace App\Exception;

use Exception;

/**
 * Исключение выбрасываемое когда в базе не получается найти нужную сущность.
 */
class EntityNotFoundException extends Exception
{

}