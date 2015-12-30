<?php
/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
namespace Diamante\UserBundle\Infrastructure;

use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\UserBundle\Entity\DiamanteUser;

interface DiamanteUserRepository extends Repository
{
    /**
     * Finds a user by email
     * @param $email
     * @return DiamanteUser
     */
    public function findUserByEmail($email);

    /**
     * @param $query
     * @param array $fields
     * @return DiamanteUser[]
     */
    public function searchByInput($query, array $fields);

    /**
     * Find users by DataGrid input parameters
     * @param array $params
     * @return DiamanteUser[]
     */
    public function findByDataGridParams(array $params);
}
