<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\GetMeasurementsFamilyQueryInterface;
use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetMeasurementsFamilyMeasurementsAction;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetMeasurementsFamilyMeasurementsActionTest extends TestCase
{
    private ?GetMeasurementsFamilyMeasurementsAction $getMeasurementsFamilyMeasurementsAction;
    private ?GetMeasurementsFamilyQueryInterface $getMeasurementsFamilyQuery;

    protected function setUp(): void
    {
        $this->getMeasurementsFamilyQuery = $this->createMock(GetMeasurementsFamilyQueryInterface::class);
        $this->getMeasurementsFamilyMeasurementsAction = new GetMeasurementsFamilyMeasurementsAction(
            $this->getMeasurementsFamilyQuery
        );
    }

    public function testItCallsTheSearchQueryWhenCodesIsEmpty(): void
    {
        $this->getMeasurementsFamilyQuery->expects($this->once())
            ->method('execute')
            ->with('Weight', 'en_US')
            ->willReturn(['units' => []]);

        ($this->getMeasurementsFamilyMeasurementsAction)(
            new Request(
                query: [],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            ),
            'Weight'
        );
    }

    public function testItRedirectsIfTheRequestIsNotAnXMLHTTPRequest(): void
    {
        $this->assertInstanceOf(
            RedirectResponse::class,
            ($this->getMeasurementsFamilyMeasurementsAction)(new Request(),
            'code')
        );
    }

    public function testItAnswersABadRequestIfTheQueryIsInvalid(): void
    {
        $this->expectException(BadRequestHttpException::class);

        ($this->getMeasurementsFamilyMeasurementsAction)(
            new Request(
                query: [
                    'locale' => 42,
                ],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            ),
            'code'
        );
    }

    public function testItThrowsANotFoundHttpException(): void
    {
        $this->expectException(NotFoundHttpException::class);

        ($this->getMeasurementsFamilyMeasurementsAction)(
            new Request(
                query: [],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            ),
            'not_existing_code'
        );
    }
}
