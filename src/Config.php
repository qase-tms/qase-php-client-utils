<?php

declare(strict_types=1);

namespace Qase\PhpClientUtils;

class Config
{
    public const REQUIRED_PARAMS = [
        'QASE_PROJECT_CODE',
        'QASE_API_BASE_URL',
        'QASE_API_TOKEN',
    ];

    private string $reporterName;
    private bool $isReportingEnabled;
    private string $projectCode;
    private string $runDescription;
    private string $baseUrl;
    private string $apiToken;
    private ?int $runId;
    private ?int $environmentId;
    private bool $isLoggingEnabled;
    private ?string $rootSuiteTitle;
    private bool $completeRunAfterSubmit;

    public function __construct(string $reporterName)
    {
        $this->reporterName = $reporterName;

        foreach ($_ENV as $envName => $envValue) {
            if (strpos($envName, "QASE_") === 0 && getenv($envName) === false) {
                putenv($envName . '=' . $envValue);
            }
        }

        $this->validate();

        $this->isReportingEnabled = getenv('QASE_REPORT') === '1';

        $this->baseUrl = getenv('QASE_API_BASE_URL');
        $this->apiToken = getenv('QASE_API_TOKEN');
        $this->projectCode = getenv('QASE_PROJECT_CODE');
        $this->runDescription = $this->defineRunDescription();
        $this->environmentId = getenv('QASE_ENVIRONMENT_ID') ? (int)getenv('QASE_ENVIRONMENT_ID') : null;
        $this->isLoggingEnabled = getenv('QASE_LOGGING') === '1' || getenv("QASE_LOGGING") === false;
        $this->rootSuiteTitle = getenv('QASE_ROOT_SUITE_TITLE') ?: null;

        $this->runId = getenv('QASE_RUN_ID') ? (int)getenv('QASE_RUN_ID') : null;
        $this->completeRunAfterSubmit = is_null($this->runId) || getenv('QASE_RUN_COMPLETE') === '1';
    }

    public function isReportingEnabled(): bool
    {
        return $this->isReportingEnabled;
    }

    public function getProjectCode(): string
    {
        return $this->projectCode;
    }

    public function getRunDescription(): string
    {
        return $this->runDescription;
    }

    public function getEnvironmentId(): ?int
    {
        return $this->environmentId;
    }

    public function isLoggingEnabled(): bool
    {
        return $this->isLoggingEnabled;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getApiToken(): string
    {
        return $this->apiToken;
    }

    public function getRunId(): ?int
    {
        return $this->runId;
    }

    public function getCompleteRunAfterSubmit(): bool
    {
        return $this->completeRunAfterSubmit;
    }

    public function getRootSuiteTitle(): ?string
    {
        return $this->rootSuiteTitle;
    }

    public function validate(): void
    {
        foreach (self::REQUIRED_PARAMS as $paramName) {
            if (!getenv($paramName)) {
                $invalidParams[] = $paramName;
            }
        }
        if (!empty($invalidParams)) {
            throw new \LogicException(sprintf(
                'The Qase %s reporter needs the following environment variable(s) to be set: %s.',
                $this->reporterName,
                implode(', ', $invalidParams)
            ));
        }
    }

    private function defineRunDescription(): string
    {
        if (getenv('QASE_RUN_DESCRIPTION') === '') {
            return '';
        }

        return getenv('QASE_RUN_DESCRIPTION') ?: "{$this->reporterName} automated run";
    }
}
