<?php

namespace AkuCmsFactory\Controller;

use Exception;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Twig\Environment as Environment;

use AkuCmsFactory\Services\CmsElementService;

#[Route(defaults: ['_routeScope' => ['api']])]
class RenderController extends AbstractController {
    #[Route(path: '/api/aku/cms-factory/render-template', name: 'api.action.aku.cms-factory.render-template', methods: ['POST'])]
    public function renderTemplate(
        Request           $request,
        Context           $context,
        Environment       $twig,
        CmsElementService $CmsElementService): JsonResponse {
        $body = json_decode($request->getContent(), true);
        $template = isset($body['template']) ? $body['template'] : '';
        $fields = isset($body['fields']) ? $body['fields'] : [];
        $field_values = isset($body['values']) ? $body['values'] : [];
        $data = $CmsElementService->getData($fields, $field_values, $context, null);
        $data['__template'] = $template;
        $params = [
            'element' => [
                'data' => $data,
            ],
        ];
        try {
            $content = $twig->createTemplate($template)->render($params);
            return new JsonResponse([
                'content' => $content,
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}