<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\OAuth;

use Psr\Container\ContainerInterface;

/**
 * Create instance of GoogleClient using 'config' service from ContainerInterface
 *
 * Config example:
 * <code>
 *  [
 *      GoogleClientFactory::class => [
 *          'redirect_url' => 'http://mysite.com/redirect_here',
 *          'client_secret' => 'agoTSH_JfnUybU-Rt8qDlprA',
 *          'project_id' => 'app-test',
 *          'client_id' => '874482307058-hda971fuqiuc60h4qms6c2u0jemgv0vq.apps.googleusercontent.com'
 *          // ...
 *      ]
 *  ]
 * </code>
 *
 * Class GoogleClientAbstractFactory
 * @package rollun\permission\Authentication\OAuth
 */
class GoogleClientFactory
{
    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')[self::class] ?? [];
        $googleClient = new GoogleClient($config);

        return $googleClient;
    }
}
