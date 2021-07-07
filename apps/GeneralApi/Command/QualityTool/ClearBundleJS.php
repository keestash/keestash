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

namespace KSA\GeneralApi\Command\QualityTool;

use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use KSP\Core\ILogger\ILogger;
use Laminas\Config\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearBundleJS extends KeestashCommand {

    protected static $defaultName = "general-api:clear-js";

    private Config  $config;
    private ILogger $logger;

    public function __construct(
        Config $config
        , ILogger $logger
    ) {
        parent::__construct(ClearBundleJS::$defaultName);
        $this->config = $config;
        $this->logger = $logger;
    }

    protected function configure(): void {
        $this->setDescription("Removes Generated JS Files")
            ->setHelp("removes all files generated by webpack and stores as a bundle file");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $files = glob($this->config->get(ConfigProvider::INSTANCE_PATH) . "/public/js/*.js");

        if (false === $files) {
            $this->logger->debug('no js to clear, files array is empty');
            return KeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
        }

        $this->writeInfo(
            (string) json_encode($files)
            , $output
        );

        $fileCount = 0;

        foreach ($files as $file) {
            if (true === is_file($file)) {
                $removed = unlink($file);

                if (false === $removed) {
                    $this->writeError(
                        "could not remove $file"
                        , $output
                    );
                    continue;
                }
                $fileCount++;
            }
        }

        $this->writeInfo("removed $fileCount files", $output);
        return KeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;

    }

}
