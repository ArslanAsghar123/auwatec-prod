<?php declare(strict_types=1);

namespace DreiscSeoPro\Administration\Controller;

use DreiscSeoPro\Core\Content\DreiscSeoSetting\DreiscSeoSettingRepository;
use DreiscSeoPro\Core\Content\SalesChannel\SalesChannelRepository;
use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use DreiscSeoPro\Core\CustomSetting\CustomSettingSaver;
use DreiscSeoPro\Core\Foundation\CustomSettingEntity\CustomSettingEntityService;
use DreiscSeoPro\Core\Foundation\CustomSettingEntity\CustomSettingEntityStruct;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;

#[Route(defaults: ['_routeScope' => ['api']])]
class DreiscSeoCustomConfigController extends AbstractController
{
    /**
     * @var SalesChannelRepository
     */
    protected $salesChannelRepository;

    public function __construct(private readonly CustomSettingLoader $customSettingLoader, private readonly CustomSettingSaver $customSettingSaver, SalesChannelRepository $salesChannelRepository)
    {
        $this->salesChannelRepository = $salesChannelRepository;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    #[Route(path: '/api/dreisc.seo/dreisc.seo.custom.config/loadCustomConfig', defaults: ['auth_required' => true])]
    public function loadCustomConfig(Request $request): JsonResponse
    {
        /** Load the dynamic settings */
        $customSettings = $this->customSettingLoader->load()->toArray();

        /** Load the custom settings for all sales channels */
        $salesChannelCustomSettings = [];
        $entitySearchResult = $this->salesChannelRepository->search(new Criteria());
        foreach($entitySearchResult->getEntities() as $salesChannelEntity) {
            $salesChannelId = $salesChannelEntity->getId();
            $salesChannelCustomSettings[$salesChannelId] = $this->customSettingLoader->load($salesChannelId)->toArray();
        }

        /** Merge dynamic and static config */
        $config = array_merge($this->getStaticSettings(), $customSettings);

        return new JsonResponse([
            'success' => true,
            'defaultCustomSettings' => $config,
            'salesChannelCustomSettings' => $salesChannelCustomSettings
        ]);
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    #[Route(path: '/api/dreisc.seo/dreisc.seo.custom.config/saveCustomConfig', defaults: ['auth_required' => true])]
    public function saveCustomConfig(Request $request): JsonResponse
    {
        $customSettings = $request->get('customSettings');
        $salesChannelCustomSettings = $request->get('salesChannelCustomSettings');
        if(empty($customSettings)) {
            throw new \RuntimeException('Missing param: customSettings');
        }

        /** Save the settings */
        $this->customSettingSaver->save($customSettings);

        /** Save custom settings of the sales channel, if available */
        foreach($salesChannelCustomSettings as $salesChannelId => $salesChannelCustomSetting) {
            $this->customSettingSaver->save($salesChannelCustomSetting, $salesChannelId);
        }

        return new JsonResponse([
            'success' => true
        ]);
    }

    private function getStaticSettings()
    {
        return [
//            'socialMedia' => [
//                'facebookTitle' => [
//                    'lengthConfig' => [
//                        'recommendedLengthStart' => 10,
//                        'recommendedLengthEnd' => 65,
//                        'maxLength' => 70,
//                    ]
//                ],
//                'facebookDescription' => [
//                    'lengthConfig' => [
//                        'recommendedLengthStart' => 10,
//                        'recommendedLengthEnd' => 65,
//                        'maxLength' => 70,
//                    ]
//                ],
//                'twitterTitle' => [
//                    'lengthConfig' => [
//                        'recommendedLengthStart' => 10,
//                        'recommendedLengthEnd' => 35,
//                        'maxLength' => 40,
//                    ]
//                ],
//                'twitterDescription' => [
//                    'lengthConfig' => [
//                        'recommendedLengthStart' => 10,
//                        'recommendedLengthEnd' => 110,
//                        'maxLength' => 120,
//                    ]
//                ]
//            ],
            'pixelTable' => [
                'default' => 12,
                1 => 9, 2 => 9, 3 => 9, 4 => 9, 5 => 9, 6 => 9, 7 => 9, 8 => 9, 9 => 9, 10 => -1,
                11 => 9, 12 => 9, 13 => -1, 14 => 9, 15 => 9, 16 => 9, 17 => 9, 18 => 9, 19 => 9, 20 => 9,
                21 => 9, 22 => 9, 23 => 9, 24 => 9, 25 => 9, 26 => 9, 27 => 9, 28 => 9, 29 => 9, 30 => 9,
                31 => 9, 32 => 5.563, 33 => 5.563, 34 => 7.109, 35 => 11.125, 36 => 11.125, 37 => 17.797, 38 => 13.344, 39 => 1, 40 => 6.672,
                41 => 6.672, 42 => 7.797, 43 => 11.688, 44 => 5.563, 45 => 6,672, 46 => 5.563, 47 => 5.563, 48 => 11.125, 49 => 11.125, 50 => 11.125,
                51 => 11.125, 52 => 11.125, 53 => 11.125, 54 => 11.125, 55 => 11.125, 56 => 11.125, 57 => 11.125, 58 => 5.563, 59 => 5.563, 60 => 11.688,
                61 => 11.688, 62 => 11.688, 63 => 11.125, 64 => 17, 65 => 13.344, 66 => 13.344, 67 => 14.453 , 68 => 14.453, 69 => 13.344, 70 => 12.219,
                71 => 15.563, 72 => 14.453, 73 => 5.563, 74 => 10, 75 => 13.344, 76 => 11.125, 77 => 16.672, 78 => 14.453, 79 => 15.563, 80 => 13.344,
                81 => 15.563, 82 => 14.453, 83 => 13.344, 84 => 12.219, 85 => 14.453, 86 => 13.344, 87 => 18.891, 88 => 13.344, 89 => 13.344, 90 => 12.219,
                91 => 5.563, 92 => 5.563, 93 => 5.563, 94 => 11.125, 95 => 11.125, 96 => 6.672, 97 => 11.125, 98 => 11.125, 99 => 10, 100 => 11.125,
                101 => 11.125, 102 => 5.563, 103 => 11.125, 104 => 11.125, 105 => 4.453, 106 => 4.453, 107 => 10, 108 => 4.453, 109 => 16.672, 110 => 11.125,
                111 => 11.125, 112 => 11.125, 113 => 11.125, 114 => 6.672, 115 => 10, 116 => 5.563, 117 => 11.125, 118 => 10, 119 => 14.453, 120 => 10,
                121 => 10, 122 => 10, 123 => 6.688, 124 => 5.203, 125 => 6.688, 126 => 11.682, 127 => 9, 128 => 9, 129 => 9, 130 => 9,
                131 => 9, 132 => 9, 133 => 9, 134 => 9, 135 => 9, 136 => 9, 137 => 9, 138 => 9, 139 => 9, 140 => 9,
                141 => 9, 142 => 9, 143 => 9, 144 => 9, 145 => 9, 146 => 9, 147 => 9, 148 => 9, 149 => 9, 150 => 9,
                151 => 9, 152 => 9, 153 => 9, 154 => 9, 155 => 9, 156 => 9, 157 => 9, 158 => 9, 159 => 9, 160 => -1,
                161 => 1, 162 => 8, 163 => 8, 164 => 9, 165 => 9, 166 => 1, 167 => 11.125, 168 => 5, 169 => 13, 170 => 6,
                171 => 8, 172 => 8, 173 => 4, 174 => 13, 175 => 11, 176 => 4, 177 => 9, 178 => 5, 179 => 5, 180 => 2,
                181 => 8, 182 => 9, 183 => 1, 184 => 2, 185 => 3, 186 => 6, 187 => 8, 188 => 14, 189 => 14, 190 => 15,
                191 => 9, 192 => 14, 193 => 14, 194 => 14, 195 => 14, 196 => 13.344, 197 => 14, 198 => 18, 199 => 12, 200 => 9,
                201 => 9, 202 => 9, 203 => 9, 204 => 2, 205 => 2, 206 => 7, 207 => 5, 208 => 12, 209 => 10, 210 => 12,
                211 => 12, 212 => 12, 213 => 12, 214 => 15.563, 215 => 6, 216 => 13, 217 => 10, 218 => 10, 219 => 10, 220 => 14.453,
                221 => 11, 222 => 9, 223 => 12.219, 224 => 9, 225 => 9, 226 => 9, 227 => 9, 228 => 11.125, 229 => 9, 230 => 15,
                231 => 7, 232 => 11.125, 233 => 11.125, 234 => 8, 235 => 8, 236 => 2, 237 => 2, 238 => 7, 239 => 5, 240 => 8,
                241 => 7, 242 => 8, 243 => 8, 244 => 8, 245 => 8, 246 => 11.125, 247 => 9, 248 => 9, 249 => 7, 250 => 7,
                251 => 7, 252 => 11.125, 253 => 10, 254 => 8, 255 => 10,
                9733 => 17.938,
                11088 => 24.953,
                10003 => 16.766
            ]
        ];
    }
}
