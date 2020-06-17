<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Platform\Search\Mapper;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Pagerfanta\Pagerfanta;

class PagerSearchContentToDataMapper
{
    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $userLanguagePreferenceProvider;

    /** @var \eZ\Publish\Core\Helper\TranslationHelper */
    protected $translationHelper;

    /** @var \eZ\Publish\API\Repository\LanguageService */
    private $languageService;

    public function __construct(
        ContentTypeService $contentTypeService,
        UserService $userService,
        UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        TranslationHelper $translationHelper,
        LanguageService $languageService
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->userService = $userService;
        $this->userLanguagePreferenceProvider = $userLanguagePreferenceProvider;
        $this->translationHelper = $translationHelper;
        $this->languageService = $languageService;
    }


    public function map(Pagerfanta $pager): array
    {
        $data = [];
        $contentTypeIds = [];

        /** @var \eZ\Publish\API\Repository\Values\Content\Search\SearchHit $searchHit */
        foreach ($pager as $searchHit) {
            /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
            $content = $searchHit->valueObject;
            $contentInfo = $content->contentInfo;

            $contentTypeIds[] = $contentInfo->contentTypeId;
            $data[] = [
                'content' => $content,
                'contentTypeId' => $contentInfo->contentTypeId,
                'contentId' => $content->id,
                'mainLocationId' => $contentInfo->mainLocationId,
                'name' => $this->translationHelper->getTranslatedContentName(
                    $content,
                    $searchHit->matchedTranslation
                ),
                'language' => $contentInfo->mainLanguageCode,
                'contributor' => $this->getContributor($contentInfo),
                'version' => $content->versionInfo->versionNo,
                'content_type' => $content->getContentType(),
                'modified' => $content->versionInfo->modificationDate,
                'initialLanguageCode' => $content->versionInfo->initialLanguageCode,
                'content_is_user' => $this->isContentIsUser($content),
                'available_enabled_translations' => $this->getAvailableTranslations($content, true),
                'available_translations' => $this->getAvailableTranslations($content),
                'translation_language_code' => $searchHit->matchedTranslation,
            ];
        }

        $this->setTranslatedContentTypesNames($data, $contentTypeIds);

        return $data;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param bool $filterDisabled
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language[]
     */
    protected function getAvailableTranslations(
        Content $content,
        bool $filterDisabled = false
    ): iterable {
        $availableTranslationsLanguages = $this->languageService->loadLanguageListByCode(
            $content->versionInfo->languageCodes
        );

        if (false === $filterDisabled) {
            return $availableTranslationsLanguages;
        }

        return array_filter(
            $availableTranslationsLanguages,
            (static function (Language $language): bool {
                return $language->enabled;
            })
        );
    }

    protected function isContentIsUser(Content $content): bool
    {
        return $this->userService->isUser($content);
    }

    protected function getContributor(ContentInfo $contentInfo): ?User
    {
        try {
            return $this->userService->loadUser($contentInfo->ownerId);
        } catch (NotFoundException $e) {
            return null;
        }
    }

    /**
     * @param array $data
     * @param int[] $contentTypeIds
     */
    protected function setTranslatedContentTypesNames(array &$data, array $contentTypeIds): void
    {
        // load list of Content Types with proper translated names
        $contentTypes = $this->contentTypeService->loadContentTypeList(
            array_unique($contentTypeIds),
            $this->userLanguagePreferenceProvider->getPreferredLanguages()
        );

        foreach ($data as $idx => $item) {
            // get content type from bulk-loaded list or fallback to lazy loaded one if not present
            $contentTypeId = $item['contentTypeId'];
            /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType */
            $contentType = $contentTypes[$contentTypeId] ?? $item['content']->getContentType();

            $data[$idx]['type'] = $contentType->getName();
            unset($data[$idx]['content'], $data[$idx]['contentTypeId']);
        }
    }
}
