<?php

namespace spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\UndefinedJobException;
use PhpSpec\ObjectBehavior;

class JobRegistrySpec extends ObjectBehavior
{
    function let(
        FeatureFlags $featureFlags,
        JobInterface $referenceEntityJob,
        JobInterface $assetJob,
        JobInterface $productExportJob,
    ) {
        $referenceEntityJob->getName()->willReturn('reference_entity_job');
        $assetJob->getName()->willReturn('asset_manager_job');
        $productExportJob->getName()->willReturn('product_export_job');

        $featureFlags->isEnabled('asset_manager')->willReturn(true);
        $featureFlags->isEnabled('reference_entity')->willReturn(false);

        $this->beConstructedWith($featureFlags);
        $this->register($referenceEntityJob, 'import', 'connector_1', 'reference_entity');
        $this->register($assetJob, 'import', 'connector_2', 'asset_manager');
        $this->register($productExportJob, 'export', 'connector_2');
    }

    function it_gets_a_job_activated_through_feature_flag(JobInterface $assetJob)
    {
        $this->get('asset_manager_job')->shouldReturn($assetJob);
    }

    function it_throws_an_exception_when_getting_a_job_deactivated_through_feature_flag(JobInterface $referenceEntityJob)
    {
        $this->shouldThrow(UndefinedJobException::class)->during('get', ['reference_entity_job']);
    }

    function it_gets_a_job_when_no_feature_flag_configured_for_it(JobInterface $productExportJob)
    {
        $this->get('product_export_job')->shouldReturn($productExportJob);
    }

    function it_throws_an_exception_when_getting_a_non_existing_job(JobInterface $referenceEntityJob)
    {
        $this->shouldThrow(UndefinedJobException::class)->during('get', ['foo']);
    }

    function it_gets_all_activated_jobs_through_feature_flags(JobInterface $assetJob, JobInterface $productExportJob)
    {
        $this->all()->shouldReturn(['asset_manager_job' => $assetJob, 'product_export_job' => $productExportJob]);
    }

    function it_gets_all_by_type(JobInterface $assetJob, JobInterface $productExportJob)
    {
        $this->allByType('import')->shouldReturn(['asset_manager_job' => $assetJob]);
    }

    function it_gets_all_by_type_group_by_connector(JobInterface $assetJob, JobInterface $productExportJob)
    {
        $this->allByTypeGroupByConnector('import')->shouldReturn(['connector_2' => ['asset_manager_job' => $assetJob]]);
    }

    function it_gets_connectors()
    {
        $this->getConnectors('import')->shouldReturn(['asset_manager_job' => 'connector_2']);
    }

}