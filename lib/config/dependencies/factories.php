<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

use Doctrine\DBAL\Connection;
use doganoo\DIP\DateTime\DateTimeService;
use doganoo\DIP\Object\String\StringService;
use GuzzleHttp\Client;
use Keestash\Api\PingHandler;
use Keestash\Command\App\InstallApps;
use Keestash\Command\App\ListAll;
use Keestash\Command\CreateSystemUser;
use Keestash\Command\Derivation\AddDerivation;
use Keestash\Command\Derivation\ClearDerivation;
use Keestash\Command\Derivation\DerivationList;
use Keestash\Command\Environment\Environment;
use Keestash\Command\Environment\ListEnvironment;
use Keestash\Command\Event\ListEvents;
use Keestash\Command\Install\CreateConfig;
use Keestash\Command\Install\InstanceData;
use Keestash\Command\Install\Uninstall;
use Keestash\Command\Permission\Add;
use Keestash\Command\Permission\CreatePermissions;
use Keestash\Command\Permission\Get;
use Keestash\Command\Permission\PermissionsByRole;
use Keestash\Command\Permission\Role\AssignPermissionsToRoles;
use Keestash\Command\Permission\Role\AssignPermissionToRole;
use Keestash\Command\Permission\Role\CreateRoles;
use Keestash\Command\Permission\Role\RemovePermissionFromRole;
use Keestash\Command\Ping;
use Keestash\Command\RateLimit\ClearRateLimiterFile;
use Keestash\Command\Role\AssignRoleToUser;
use Keestash\Command\Role\RolesByUser;
use Keestash\Command\Routes;
use Keestash\Command\Security\Cors;
use Keestash\Command\Worker\Queue\QueueDelete;
use Keestash\Command\Worker\Queue\QueueList;
use Keestash\Command\Worker\Queue\Reset;
use Keestash\Command\Worker\WorkerFlusher;
use Keestash\Command\Worker\WorkerLocker;
use Keestash\Command\Worker\WorkerRunner;
use Keestash\Core\Backend\SQLBackend\MySQLBackend;
use Keestash\Core\DTO\Event\Listener\RemoveOutdatedTokens;
use Keestash\Core\DTO\Event\Listener\SendSummaryMail;
use Keestash\Core\Repository\ApiLog\ApiLogRepository;
use Keestash\Core\Repository\AppRepository\AppRepository;
use Keestash\Core\Repository\Derivation\DerivationRepository;
use Keestash\Core\Repository\EncryptionKey\Organization\OrganizationKeyRepository;
use Keestash\Core\Repository\EncryptionKey\User\UserKeyRepository;
use Keestash\Core\Repository\File\FileRepository;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Repository\Instance\InstanceRepository;
use Keestash\Core\Repository\Job\JobRepository;
use Keestash\Core\Repository\LDAP\DefaultConnectionRepository;
use Keestash\Core\Repository\LDAP\DefaultLDAPRepository;
use Keestash\Core\Repository\MailLog\MailLogRepository;
use Keestash\Core\Repository\Payment\DefaultPaymentLogRepository;
use Keestash\Core\Repository\Queue\QueueRepository;
use Keestash\Core\Repository\RBAC\RBACRepository;
use Keestash\Core\Repository\Token\TokenRepository;
use Keestash\Core\Repository\User\UserRepository;
use Keestash\Core\Repository\User\UserStateRepository;
use Keestash\Core\Service\App\AppService;
use Keestash\Core\Service\App\InstallerService;
use Keestash\Core\Service\App\LoaderService;
use Keestash\Core\Service\Cache\NullService;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\Config\IniConfigService;
use Keestash\Core\Service\Core\Access\AccessService;
use Keestash\Core\Service\Core\Environment\EnvironmentService;
use Keestash\Core\Service\Core\Language\LanguageService;
use Keestash\Core\Service\Core\Locale\LocaleService;
use Keestash\Core\Service\CSV\CSVService;
use Keestash\Core\Service\Derivation\DerivationService;
use Keestash\Core\Service\Email\EmailService;
use Keestash\Core\Service\Encryption\Base64Service;
use Keestash\Core\Service\Encryption\Credential\DerivedCredentialService;
use Keestash\Core\Service\Encryption\Encryption\KeestashEncryptionService;
use Keestash\Core\Service\Encryption\Key\KeyService;
use Keestash\Core\Service\Encryption\Mask\StringMaskService;
use Keestash\Core\Service\Encryption\Password\PasswordService;
use Keestash\Core\Service\Event\EventService;
use Keestash\Core\Service\Event\Listener\RolesAndPermissionsListener;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\Icon\IconService;
use Keestash\Core\Service\File\Mime\MimeTypeService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use Keestash\Core\Service\HTTP\CORS\ProjectConfiguration;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\HTTP\Input\SanitizerService;
use Keestash\Core\Service\HTTP\JWTService;
use Keestash\Core\Service\HTTP\ResponseService;
use Keestash\Core\Service\HTTP\Route\RouteService;
use Keestash\Core\Service\L10N\GetText;
use Keestash\Core\Service\LDAP\LDAPService;
use Keestash\Core\Service\Organization\OrganizationService;
use Keestash\Core\Service\Payment\DefaultPaymentService;
use Keestash\Core\Service\Permission\PermissionService;
use Keestash\Core\Service\Permission\RoleService;
use Keestash\Core\Service\Phinx\Migrator;
use Keestash\Core\Service\Queue\QueueService;
use Keestash\Core\Service\ReflectionService;
use Keestash\Core\Service\Router\ApiRequestService;
use Keestash\Core\Service\Router\RouterService;
use Keestash\Core\Service\Router\VerificationService;
use Keestash\Core\Service\User\Repository\UserRepositoryService;
use Keestash\Core\Service\User\UserService;
use Keestash\Core\System\Application;
use Keestash\Factory\Command\App\InstallAppsFactory;
use Keestash\Factory\Command\App\ListAllFactory;
use Keestash\Factory\Command\CreateSystemUserFactory;
use Keestash\Factory\Command\Derivation\AddDerivationFactory;
use Keestash\Factory\Command\Derivation\ClearDerivationFactory;
use Keestash\Factory\Command\Derivation\DerivationListFactory;
use Keestash\Factory\Command\Environment\EnvironmentFactory;
use Keestash\Factory\Command\Environment\ListEnvironmentFactory;
use Keestash\Factory\Command\Event\EventsFactory;
use Keestash\Factory\Command\Install\CreateConfigFactory;
use Keestash\Factory\Command\Install\InstanceDataFactory;
use Keestash\Factory\Command\Install\UninstallFactory;
use Keestash\Factory\Command\Permission\AddFactory;
use Keestash\Factory\Command\Permission\CreatePermissionsFactory;
use Keestash\Factory\Command\Permission\GetFactory;
use Keestash\Factory\Command\Permission\PermissionsByRoleFactory;
use Keestash\Factory\Command\Permission\Role\AssignPermissionsToRolesFactory;
use Keestash\Factory\Command\Permission\Role\AssignPermissionToRoleFactory;
use Keestash\Factory\Command\Permission\Role\CreateRolesFactory;
use Keestash\Factory\Command\Permission\Role\RemovePermissionFromRoleFactory;
use Keestash\Factory\Command\RateLimit\ClearRateLimiterFileFactory;
use Keestash\Factory\Command\Role\AssignRoleToUserFactory;
use Keestash\Factory\Command\Role\RolesByUserFactory;
use Keestash\Factory\Command\RoutesFactory;
use Keestash\Factory\Command\Security\CorsFactory;
use Keestash\Factory\Command\Worker\Queue\QueueDeleteFactory;
use Keestash\Factory\Command\Worker\Queue\QueueListFactory;
use Keestash\Factory\Command\Worker\Queue\ResetFactory;
use Keestash\Factory\Command\Worker\WorkerFlusherFactory;
use Keestash\Factory\Command\Worker\WorkerLockerFactory;
use Keestash\Factory\Command\Worker\WorkerRunnerFactory;
use Keestash\Factory\Core\Backend\MySQLBackendFactory;
use Keestash\Factory\Core\Builder\Validator\EmailValidatorFactory;
use Keestash\Factory\Core\Builder\Validator\PhoneValidatorFactory;
use Keestash\Factory\Core\Builder\Validator\UriValidatorFactory;
use Keestash\Factory\Core\Event\Listener\RemoveOutdatedTokensFactory;
use Keestash\Factory\Core\Event\Listener\SendSummaryMailListenerFactory;
use Keestash\Factory\Core\Legacy\LegacyFactory;
use Keestash\Factory\Core\Logger\LoggerFactory;
use Keestash\Factory\Core\Repository\ApiLogRepository\ApiLogRepositoryFactory;
use Keestash\Factory\Core\Repository\AppRepository\AppRepositoryFactory;
use Keestash\Factory\Core\Repository\DerivationRepository\DerivationRepositoryFactory;
use Keestash\Factory\Core\Repository\EncryptionKey\Organization\OrganizationKeyRepositoryFactory;
use Keestash\Factory\Core\Repository\EncryptionKey\User\UserKeyRepositoryFactory;
use Keestash\Factory\Core\Repository\File\FileRepositoryFactory;
use Keestash\Factory\Core\Repository\Instance\InstanceDBFactory;
use Keestash\Factory\Core\Repository\Instance\InstanceRepositoryFactory;
use Keestash\Factory\Core\Repository\Job\JobRepositoryFactory;
use Keestash\Factory\Core\Repository\MailLog\MailLogRepositoryFactory;
use Keestash\Factory\Core\Repository\Queue\QueueRepositoryFactory;
use Keestash\Factory\Core\Repository\RBAC\PermissionRepositoryFactory;
use Keestash\Factory\Core\Repository\Token\TokenRepositoryFactory;
use Keestash\Factory\Core\Repository\User\UserRepositoryFactory;
use Keestash\Factory\Core\Repository\User\UserStateRepositoryFactory;
use Keestash\Factory\Core\Service\App\AppServiceFactory;
use Keestash\Factory\Core\Service\App\InstallerServiceFactory;
use Keestash\Factory\Core\Service\App\LoaderServiceFactory;
use Keestash\Factory\Core\Service\Config\ConfigServiceFactory;
use Keestash\Factory\Core\Service\Core\Language\LanguageServiceFactory;
use Keestash\Factory\Core\Service\CSV\CSVServiceFactory;
use Keestash\Factory\Core\Service\Derivation\DerivationServiceFactory;
use Keestash\Factory\Core\Service\Email\EmailServiceFactory;
use Keestash\Factory\Core\Service\Encryption\Credential\DerivedCredentialServiceFactory;
use Keestash\Factory\Core\Service\Encryption\KeestashEncryptionServiceFactory;
use Keestash\Factory\Core\Service\Encryption\Key\KeyServiceFactory;
use Keestash\Factory\Core\Service\Encryption\Password\PasswordServiceFactory;
use Keestash\Factory\Core\Service\Event\EventServiceFactory;
use Keestash\Factory\Core\Service\Event\Listener\RolesAndPermissionsListenerFactory;
use Keestash\Factory\Core\Service\File\FileServiceFactory;
use Keestash\Factory\Core\Service\File\RawFile\RawFileServiceFactory;
use Keestash\Factory\Core\Service\HTTP\CORS\ProjectConfigurationFactory;
use Keestash\Factory\Core\Service\HTTP\HTTPServiceFactory;
use Keestash\Factory\Core\Service\HTTP\JWTServiceFactory;
use Keestash\Factory\Core\Service\HTTP\ResponseServiceFactory;
use Keestash\Factory\Core\Service\HTTP\SanitizerServiceFactory;
use Keestash\Factory\Core\Service\LDAP\LDAPServiceFactory;
use Keestash\Factory\Core\Service\Organization\OrganizationServiceFactory;
use Keestash\Factory\Core\Service\Permission\PermissionServiceFactory;
use Keestash\Factory\Core\Service\Permission\RoleServiceFactory;
use Keestash\Factory\Core\Service\Phinx\MigratorFactory;
use Keestash\Factory\Core\Service\Queue\QueueServiceFactory;
use Keestash\Factory\Core\Service\Router\ApiRequestServiceFactory;
use Keestash\Factory\Core\Service\Router\RouterServiceFactory;
use Keestash\Factory\Core\Service\Router\VerificationFactory;
use Keestash\Factory\Core\Service\User\Repository\UserRepositoryServiceFactory;
use Keestash\Factory\Core\Service\User\UserServiceFactory;
use Keestash\Factory\Middleware\ApplicationStartedMiddlewareFactory;
use Keestash\Factory\Middleware\CSPHeaderMiddlewareFactory;
use Keestash\Factory\Middleware\DeactivatedRouteMiddlewareFactory;
use Keestash\Factory\Middleware\DispatchMiddlewareFactory;
use Keestash\Factory\Middleware\EnvironmentMiddlewareFactory;
use Keestash\Factory\Middleware\ExceptionHandlerMiddlewareFactory as ApiExceptionHandlerMiddlewareFactory;
use Keestash\Factory\Middleware\InstanceInstalledMiddlewareFactory;
use Keestash\Factory\Middleware\KeestashHeaderMiddlewareFactory;
use Keestash\Factory\Middleware\PermissionMiddlewareFactory;
use Keestash\Factory\Middleware\RateLimiterMiddlewareFactory;
use Keestash\Factory\Middleware\SanitizeInputMiddlewareFactory;
use Keestash\Factory\Middleware\UserActiveMiddlewareFactory;
use Keestash\Factory\Queue\Handler\EventHandlerFactory;
use Keestash\Factory\ThirdParty\Doctrine\ConnectionFactory;
use Keestash\Factory\ThirdParty\doganoo\DateTimeServiceFactory;
use Keestash\Factory\ThirdParty\nikolaposa\RateLimit\FileRateLimiterFactory;
use Keestash\Middleware\ApplicationStartedMiddleware;
use Keestash\Middleware\CSPHeaderMiddleware;
use Keestash\Middleware\DeactivatedRouteMiddleware;
use Keestash\Middleware\DispatchMiddleware;
use Keestash\Middleware\EnvironmentMiddleware;
use Keestash\Middleware\ExceptionHandlerMiddleware as ApiExceptionHandlerMiddlerware;
use Keestash\Middleware\InstanceInstalledMiddleware;
use Keestash\Middleware\KeestashHeaderMiddleware;
use Keestash\Middleware\PermissionMiddleware;
use Keestash\Middleware\RateLimiterMiddleware;
use Keestash\Middleware\SanitizeInputMiddleware;
use Keestash\Middleware\UserActiveMiddleware;
use Keestash\Queue\Handler\EventHandler;
use Keestash\ThirdParty\nikolaposa\RateLimit\FileRateLimiter;
use KSA\PasswordManager\Service\Node\Edge\EdgeService;
use Laminas\I18n\Validator\PhoneNumber as PhoneValidator;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Validator\EmailAddress as EmailValidator;
use Laminas\Validator\Uri as UriValidator;
use Psr\Log\LoggerInterface;

return [
    // Api
    PingHandler::class                                        => InvokableFactory::class

    // App
    , ProjectConfiguration::class                             => ProjectConfigurationFactory::class

    // repository
    , ApiLogRepository::class                                 => ApiLogRepositoryFactory::class
    , MySQLBackend::class                                     => MySQLBackendFactory::class
    , FileRepository::class                                   => FileRepositoryFactory::class
    , UserRepository::class                                   => UserRepositoryFactory::class
    , UserKeyRepository::class                                => UserKeyRepositoryFactory::class
    , OrganizationKeyRepository::class                        => OrganizationKeyRepositoryFactory::class
    , UserStateRepository::class                              => UserStateRepositoryFactory::class
    , InstanceRepository::class                               => InstanceRepositoryFactory::class
    , TokenRepository::class                                  => TokenRepositoryFactory::class
    , AppRepository::class                                    => AppRepositoryFactory::class
    , QueueRepository::class                                  => QueueRepositoryFactory::class
    , RBACRepository::class                                   => PermissionRepositoryFactory::class
    , DefaultLDAPRepository::class                            => InvokableFactory::class
    , DefaultConnectionRepository::class                      => InvokableFactory::class
    , DefaultPaymentLogRepository::class                      => InvokableFactory::class
    , MailLogRepository::class                                => MailLogRepositoryFactory::class
    , DerivationRepository::class                             => DerivationRepositoryFactory::class

    , LoggerInterface::class                                  => LoggerFactory::class
    , Application::class                                      => LegacyFactory::class
    , EventService::class                                     => EventServiceFactory::class
    , LoaderService::class                                    => LoaderServiceFactory::class
    , VerificationService::class                              => VerificationFactory::class
    , InstanceDB::class                                       => InstanceDBFactory::class
    , Migrator::class                                         => MigratorFactory::class
    , JobRepository::class                                    => JobRepositoryFactory::class
    , JWTService::class                                       => JWTServiceFactory::class

    // builder
    , EmailValidator::class                                   => EmailValidatorFactory::class
    , PhoneValidator::class                                   => PhoneValidatorFactory::class
    , UriValidator::class                                     => UriValidatorFactory::class

    // middleware
    , InstanceInstalledMiddleware::class                      => InstanceInstalledMiddlewareFactory::class
    , DispatchMiddleware::class                               => DispatchMiddlewareFactory::class
    , ApplicationStartedMiddleware::class                     => ApplicationStartedMiddlewareFactory::class
    , RateLimiterMiddleware::class                            => RateLimiterMiddlewareFactory::class
    , PermissionMiddleware::class                             => PermissionMiddlewareFactory::class
    , EnvironmentMiddleware::class                            => EnvironmentMiddlewareFactory::class
    , CSPHeaderMiddleware::class                              => CSPHeaderMiddlewareFactory::class
    , DeactivatedRouteMiddleware::class                       => DeactivatedRouteMiddlewareFactory::class
    , SanitizeInputMiddleware::class                          => SanitizeInputMiddlewareFactory::class

    // api
    , KeestashHeaderMiddleware::class                         => KeestashHeaderMiddlewareFactory::class
    , ApiExceptionHandlerMiddlerware::class                   => ApiExceptionHandlerMiddlewareFactory::class

    // web
    , UserActiveMiddleware::class                             => UserActiveMiddlewareFactory::class

    // ThirdParty
    , DateTimeService::class                                  => DateTimeServiceFactory::class
    , Connection::class                                       => ConnectionFactory::class
    , \doganoo\DIP\HTTP\HTTPService::class                    => InvokableFactory::class
    , Client::class                                           => InvokableFactory::class
    , FileRateLimiter::class                                  => FileRateLimiterFactory::class

    // service
    , UserService::class                                      => UserServiceFactory::class
    , ConfigService::class                                    => ConfigServiceFactory::class
    , KeyService::class                                       => KeyServiceFactory::class
    , KeestashEncryptionService::class                        => KeestashEncryptionServiceFactory::class
    , FileService::class                                      => FileServiceFactory::class
    , RawFileService::class                                   => RawFileServiceFactory::class
    , DerivedCredentialService::class                         => DerivedCredentialServiceFactory::class
    , EmailService::class                                     => EmailServiceFactory::class
    , OrganizationService::class                              => OrganizationServiceFactory::class
    , InstallerService::class                                 => InstallerServiceFactory::class
    , HTTPService::class                                      => HTTPServiceFactory::class
    , \Keestash\Core\Service\Instance\InstallerService::class => \Keestash\Factory\Core\Service\Instance\InstallerServiceFactory::class
    , LanguageService::class                                  => LanguageServiceFactory::class
    , RouterService::class                                    => RouterServiceFactory::class
    , UserRepositoryService::class                            => UserRepositoryServiceFactory::class
    , \Keestash\Core\Service\File\Upload\FileService::class   => \Keestash\Factory\Core\Service\Upload\FileServiceFactory::class
    , SanitizerService::class                                 => SanitizerServiceFactory::class
    , ApiRequestService::class                                => ApiRequestServiceFactory::class
    , NullService::class                                      => InvokableFactory::class
    , ReflectionService::class                                => InvokableFactory::class
    , LocaleService::class                                    => InvokableFactory::class
    , EnvironmentService::class                               => InvokableFactory::class
    , AppService::class                                       => AppServiceFactory::class
    , EdgeService::class                                      => InvokableFactory::class
    , IconService::class                                      => InvokableFactory::class
    , PasswordService::class                                  => PasswordServiceFactory::class
    , IniConfigService::class                                 => InvokableFactory::class
    , StringService::class                                    => InvokableFactory::class
    , AccessService::class                                    => InvokableFactory::class
    , CSVService::class                                       => CSVServiceFactory::class
    , MimeTypeService::class                                  => InvokableFactory::class
    , QueueService::class                                     => QueueServiceFactory::class
    , RouteService::class                                     => InvokableFactory::class
    , LDAPService::class                                      => LDAPServiceFactory::class
    , Base64Service::class                                    => InvokableFactory::class
    , DefaultPaymentService::class                            => InvokableFactory::class
    , DerivationService::class                                => DerivationServiceFactory::class
    , PermissionService::class                                => PermissionServiceFactory::class
    , RoleService::class                                      => RoleServiceFactory::class
    , StringMaskService::class                                => InvokableFactory::class
    , ResponseService::class                                  => ResponseServiceFactory::class

    , GetText::class                                          => InvokableFactory::class
    , \doganoo\PHPUtil\HTTP\Session::class                    => InvokableFactory::class
    , HTMLPurifier::class                                     => InvokableFactory::class

    // command
    , WorkerRunner::class                                     => WorkerRunnerFactory::class
    , ListEvents::class                                       => EventsFactory::class
    , QueueList::class                                        => QueueListFactory::class
    , QueueDelete::class                                      => QueueDeleteFactory::class
    , Reset::class                                            => ResetFactory::class
    , ListAll::class                                          => ListAllFactory::class
    , ClearDerivation::class                                  => ClearDerivationFactory::class
    , AddDerivation::class                                    => AddDerivationFactory::class
    , Cors::class                                             => CorsFactory::class
    , DerivationList::class                                   => DerivationListFactory::class
    , ClearRateLimiterFile::class                             => ClearRateLimiterFileFactory::class
    , WorkerLocker::class                                     => WorkerLockerFactory::class
    , WorkerFlusher::class                                    => WorkerFlusherFactory::class
    , Routes::class                                           => RoutesFactory::class
    , Uninstall::class                                        => UninstallFactory::class
    , Ping::class                                             => InvokableFactory::class
    , Environment::class                                      => EnvironmentFactory::class
    , ListEnvironment::class                                  => ListEnvironmentFactory::class
    , CreateConfig::class                                     => CreateConfigFactory::class
    , CreatePermissions::class                                => CreatePermissionsFactory::class
    , CreateSystemUser::class                                 => CreateSystemUserFactory::class
    , InstanceData::class                                     => InstanceDataFactory::class
    , CreateRoles::class                                      => CreateRolesFactory::class
    , AssignPermissionsToRoles::class                         => AssignPermissionsToRolesFactory::class
    , InstallApps::class                                      => InstallAppsFactory::class
    , \Keestash\Command\App\Uninstall::class                  => \Keestash\Factory\Command\App\UninstallFactory::class
    , RemovePermissionFromRole::class                         => RemovePermissionFromRoleFactory::class

    // command
    // --- listener
    , RolesAndPermissionsListener::class                      => RolesAndPermissionsListenerFactory::class

    , Get::class                                              => GetFactory::class
    , \Keestash\Command\Role\Get::class                       => \Keestash\Factory\Command\Role\GetFactory::class
    , RolesByUser::class                                      => RolesByUserFactory::class
    , PermissionsByRole::class                                => PermissionsByRoleFactory::class
    , Add::class                                              => AddFactory::class
    , \Keestash\Command\Role\Add::class                       => \Keestash\Factory\Command\Role\AddFactory::class
    , AssignRoleToUser::class                                 => AssignRoleToUserFactory::class
    , AssignPermissionToRole::class                           => AssignPermissionToRoleFactory::class

    // handler
    , EventHandler::class                                     => EventHandlerFactory::class

    // events
    // ---- listener
    , RemoveOutdatedTokens::class                             => RemoveOutdatedTokensFactory::class
    , SendSummaryMail::class                                  => SendSummaryMailListenerFactory::class
];