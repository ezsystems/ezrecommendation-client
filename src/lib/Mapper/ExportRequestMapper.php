<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Mapper;

use EzSystems\EzRecommendationClient\Helper\ParamsConverterHelper;
use EzSystems\EzRecommendationClient\Value\ExportRequest;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

final class ExportRequestMapper
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \EzSystems\EzRecommendationClient\Value\ExportRequest
     */
    public function getExportRequest(Request $request): ExportRequest
    {
        $query = $request->query;

        $exportRequest = new ExportRequest();
        $exportRequest->customerId = $this->getCustomerId($query);
        $exportRequest->licenseKey = $this->getLicenseKey($query);
        $exportRequest->path = $this->getPath($query);
        $exportRequest->hidden = $this->getVisibility($query);
        $exportRequest->image = $this->getImage($query);
        $exportRequest->siteaccess = $this->getSiteAccess($query);
        $exportRequest->webHook = $this->getWebHook($query);
        $exportRequest->fields = $this->getFields($query);
        $exportRequest->pageSize = (int) $query->get('pageSize', null);
        $exportRequest->page = (int) $query->get('page', 1);
        $exportRequest->documentRoot = $request->server->get('DOCUMENT_ROOT');
        $exportRequest->host = $request->getSchemeAndHttpHost();
        $exportRequest->contentTypeIdList = ParamsConverterHelper::getIdListFromString($request->get('idList'));
        $exportRequest->languages = $this->getLang($query);

        return $exportRequest;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag $parameterBag
     *
     * @return string|null
     */
    private function getPath(ParameterBag $parameterBag): ?string
    {
        $path = $parameterBag->get('path');

        return $path && preg_match('/^\/\d+(?:\/\d+)*\/$/', $path) === 1 ? $path : null;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag $parameterBag
     *
     * @return int
     */
    private function getVisibility(ParameterBag $parameterBag): int
    {
        return (int) $parameterBag->get('hidden', 0);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag $parameterBag
     *
     * @return string|null
     */
    private function getImage(ParameterBag $parameterBag): ?string
    {
        $image = $parameterBag->get('image');

        return ($image && preg_match('/^[a-zA-Z0-9\-\_]+$/', $image) === 1) ? $image : null;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag $parameterBag
     *
     * @return string|null
     */
    private function getSiteAccess(ParameterBag $parameterBag): ?string
    {
        $siteAccess = $parameterBag->get('siteaccess');

        return $siteAccess && preg_match('/^[a-zA-Z0-9_-]+$/', $siteAccess) === 1 ? $siteAccess : null;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag $parameterBag
     *
     * @return string|null
     */
    private function getWebHook(ParameterBag $parameterBag): ?string
    {
        $webHook = $parameterBag->get('webHook');

        return $webHook && preg_match(
            '/((http|https)\:\/\/)?[a-zA-Z0-9\.\/\?\:@\-_=#]+\.([a-zA-Z0-9\&\.\/\?\:@\-_=#])*/',
            $webHook
        ) === 1 ? $webHook : null;
    }
    
    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag $parameterBag
     *
     * @return string|null
     */
    private function getFields(ParameterBag $parameterBag): ?string
    {
        $fields = $parameterBag->get('fields');

        return $fields && preg_match('/^[a-zA-Z0-9\-\_\,]+$/', $fields) === 1 ? $fields : null;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag $parameterBag
     *
     * @return string|null
     */
    private function getCustomerId(ParameterBag $parameterBag): ?string
    {
        $customerId = $parameterBag->get('customerId');

        return $customerId && $this->validateAuthenticationParameter($customerId) ? $customerId : null;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag $parameterBag
     *
     * @return string|null
     */
    private function getLicenseKey(ParameterBag $parameterBag): ?string
    {
        $licenseKey = $parameterBag->get('licenseKey');

        return $licenseKey && $this->validateAuthenticationParameter($licenseKey) ? $licenseKey : null;
    }

    /**
     * @param string $parameter
     *
     * @return bool
     */
    private function validateAuthenticationParameter(string $parameter): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9_-]+$/', $parameter);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag $parameterBag
     *
     * @return string
     */
    private function getLang(ParameterBag $parameterBag): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]+/', '', $parameterBag->get('lang'));
    }
}
