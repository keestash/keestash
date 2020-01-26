<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace KSP\Core\Manager\RouterManager;

use KSP\Core\Manager\IManager;

interface IRouterManager extends IManager {

    public const DELETE      = "DELETE";
    public const GET         = "GET";
    public const POST        = "POST";
    public const PUT         = "PUT";

    public const API_ROUTER  = "router.api";
    public const HTTP_ROUTER = "router.http";

    public function add(string $name, IRouter $router): bool;

    public function get(string $name): ?IRouter;

}