<?php

namespace App\Exception;

use Exception;

/**
 * Исключение выбрасываемое когда не получается сохранить в базе какую-либо сущность.
 */
class EntityNotSavedException extends Exception
{

}