<?php

declare(strict_types=1);

namespace Qase\PhpClientUtils;

use Qase\Client\ApiException;
use Qase\Client\Model\Response;
use Qase\Client\Model\ResultCreateBulk;
use Qase\Client\Model\RunCreate;

class ResultHandler
{

    private LoggerInterface $logger;
    private Repository $repo;
    private ResultsConverter $resultsConverter;

    public function __construct(Repository $repo, ResultsConverter $resultsConverter, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->repo = $repo;
        $this->resultsConverter = $resultsConverter;
    }

    /**
     * @throws ApiException
     */
    public function handle(RunResult $runResult, string $rootSuiteTitle): ?Response
    {
        $this->logger->writeln('', '');
        $this->logger->writeln('Results handling started');

        $bulkResults = $this->resultsConverter->prepareBulkResults($runResult, $rootSuiteTitle);

        if ($bulkResults === []) {
            $this->logger->writeln('WARNING: did not find any tests to report in the Qase TMS');
            return null;
        }

        return $this->submit($runResult, $bulkResults);
    }

    /**
     * @throws ApiException
     */
    private function submit(RunResult $runResult, array $bulkResults): Response
    {
        if ($runResult->getConfig()->getRunId()) {
            $runId = $runResult->getConfig()->getRunId();
        } else {
            $runId = $this->createRunId(
                $runResult->getConfig()->getProjectCode(),
                $runResult->getConfig()->getEnvironmentId(),
                $runResult->getConfig()->getRunDescription(),
                $runResult->getConfig()->getRunName()
            );
        }

        $this->logger->write("publishing results for run #{$runId}... ");

        $response = $this->repo->getResultsApi()->createResultBulk(
            $runResult->getConfig()->getProjectCode(),
            $runId,
            new ResultCreateBulk(['results' => $bulkResults])
        );

        $this->logger->writeln('OK', '');

        if ($runResult->getConfig()->getCompleteRunAfterSubmit()) {
            $this->logger->write("completing run #{$runId}... ");

            $this->repo->getRunsApi()->completeRun($runResult->getConfig()->getProjectCode(), $runId);

            $this->logger->writeln('OK', '');
        }

        return $response;
    }

    /**
     * @throws ApiException
     */
    public function createRunId(string $projectCode, ?int $environmentId, string $description, ?string $name): int
    {
        $runName = $name ?: 'Automated run ' . date('Y-m-d H:i:s');
        $runBody = new RunCreate([
            "title" => $runName,
            "description" => $description,
            'isAutotest' => true,
            'environmentId' => $environmentId,
        ]);

        $this->logger->write("creating run '{$runName}'... ");

        $response = $this->repo->getRunsApi()->createRun($projectCode, $runBody);

        if ($response->getResult() === null) {
            throw new \RuntimeException('Could not create run');
        }

        $this->logger->writeln('OK', '');

        return $response->getResult()->getId();
    }
}
