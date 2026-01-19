<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Controller;

use Swpa\SwpaBackup\Service\Config;
use Swpa\SwpaBackup\Service\Manager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Backup controller
 *
 * @package   Swpa\SwpaBackup\Controller
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class BackupController extends AbstractController
{
    public function __construct(protected Config $config, protected Manager $manager)
    {
    }
    
    #[Route(path: '/api/_action/swpa-backup/create', name: 'api.action.swpa-backup.create', defaults: ['_routeScope' => ['administration']], methods: ['POST'])]
    public function create(): JsonResponse
    {
        $result = true;
        $message = [];
        try {
            $this->manager->makeBackup(true);
        } catch (\Exception $e) {
            $result = false;
            $message[] = $e->getMessage();
        }
        
        try {
            $this->manager->checkScheduledTasks();
        } catch (\Exception $e) {
            $result = false;
            $message[] = $e->getMessage();
        }
        
        return new JsonResponse(['result' => $result, 'time' => 10, 'message' => $message]);
    }
    
}
