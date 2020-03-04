<?php

declare(strict_types=1);

namespace Buddy\Repman\Tests\Unit\Service\GitLabApi;

use Buddy\Repman\Service\GitLabApi\MattGitLabApi;
use Buddy\Repman\Service\GitLabApi\Project;
use Gitlab\Api\Projects;
use Gitlab\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MattGitLabApiTest extends TestCase
{
    /**
     * @var MockObject|Client
     */
    private $clientMock;

    private MattGitLabApi $api;

    protected function setUp(): void
    {
        $this->clientMock = $this->getMockBuilder(Client::class)->getMock();
        $this->clientMock->expects($this->once())->method('authenticate');

        $this->api = new MattGitLabApi($this->clientMock);
    }

    public function testFetchUserProjects(): void
    {
        $projects = $this->getMockBuilder(Projects::class)->disableOriginalConstructor()->getMock();
        $projects->method('all')->willReturn([
            [
                'id' => 17275574,
                'path' => 'left-pad',
                'path_with_namespace' => 'repman/left-pad',
                'created_at' => '2020-03-04T08:06:05.204Z',
                'default_branch' => 'master',
                'ssh_url_to_repo' => 'git@gitlab.com:repman/left-pad.git',
                'http_url_to_repo' => 'https://gitlab.com/repman/left-pad.git',
                'web_url' => 'https://gitlab.com/repman/left-pad',
            ],
            [
                'id' => 17275573,
                'path' => 'right-pad',
                'path_with_namespace' => 'repman/right-pad',
                'created_at' => '2020-03-04T08:06:05.204Z',
                'default_branch' => 'master',
                'ssh_url_to_repo' => 'git@gitlab.com:repman/right-pad.git',
                'http_url_to_repo' => 'https://gitlab.com/repman/right-pad.git',
                'web_url' => 'https://gitlab.com/repman/right-pad',
            ],
        ]);
        $this->clientMock->method('projects')->willReturn($projects);

        self::assertEquals([
            new Project(17275574, 'repman/left-pad', 'https://gitlab.com/repman/left-pad'),
            new Project(17275573, 'repman/right-pad', 'https://gitlab.com/repman/right-pad'),
        ], $this->api->projects('gitlab-token'));
    }

    public function testAddHookWhenNotExist(): void
    {
        $projects = $this->getMockBuilder(Projects::class)->disableOriginalConstructor()->getMock();
        $projects->method('hooks')->willReturn([
            [
                'id' => 1834838,
                'url' => 'https://repman.wip/hook',
                'created_at' => '2020-03-04T10:26:45.746Z',
                'push_events' => true,
            ],
        ]);

        $projects->expects($this->once())->method('addHook');
        $this->clientMock->method('projects')->willReturn($projects);

        $this->api->addHook('token', 123, 'https://webhook.url');
    }

    public function testDoNotAddHookWhenExist(): void
    {
        $projects = $this->getMockBuilder(Projects::class)->disableOriginalConstructor()->getMock();
        $projects->method('hooks')->willReturn([
            [
                'id' => 1834838,
                'url' => 'https://webhook.url',
                'created_at' => '2020-03-04T10:26:45.746Z',
                'push_events' => true,
            ],
        ]);

        $projects->expects($this->never())->method('addHook');
        $this->clientMock->method('projects')->willReturn($projects);

        $this->api->addHook('token', 123, 'https://webhook.url');
    }
}
