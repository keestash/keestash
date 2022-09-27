<?php
declare(strict_types=1);
/**
 * server
 *
 * Copyright (C) <2020> <Dogan Ucar>
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


namespace Keestash\Core\Service\Config;

class IniConfigService implements \KSP\Core\Service\Config\IniConfigService {

    public function getValue(string $key, $default = null) {
        $ini = ini_get($key);
        if ("" === $ini || false === $ini) return $default;
        return $ini;
    }

    public function toBytes(string $value): int {
        $value = trim($value);
        $last  = strtolower($value[strlen($value) - 1]);
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        return (int) $value;
    }

}
