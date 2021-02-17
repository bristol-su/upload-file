<?php


namespace BristolSU\Module\Tests\UploadFile\Http\Controllers\AdminApi;


use BristolSU\Module\Tests\UploadFile\TestCase;
use BristolSU\Module\UploadFile\Models\Comment;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Module\UploadFile\Models\FileStatus;

class FileSearchControllerTest extends TestCase
{

    /** @test */
    public function it_filters_results_by_all_fields_if_filter_on_not_given(){
        $this->bypassAuthorization();

        $file1 = $this->createFile('My Title One', 'My Description AA', 'myfilename.png', ['tag1', 'tag2'], 'Approved', ['Comment ten', 'Comment two']);
        $file2 = $this->createFile('My Title Two', 'My Description One', 'myfilename.png', ['tag1', 'tag2'], 'Approved', ['Comment ten', 'Comment two']);
        $file3 = $this->createFile('My Title Three', 'My Description', 'myfilename One test.png', ['tag1', 'tag2'], 'Approved', ['Comment ten', 'Comment two']);
        $file4 = $this->createFile('My Title Four', 'My Description', 'myfilename.png', ['tag1', 'tag2'], 'Approved', ['Comment ten', 'Comment two']);
        $file5 = $this->createFile('My Title Five', 'My Description', 'myfilename.png', ['tag1', 'tag2'], 'Approved', ['Comment ten', 'Comment two']);
        $file6 = $this->createFile('My Title Six', 'My Description', 'myfilename.png', ['tag1', 'tag2'], 'Approved', ['Comment One', 'Comment two']);
        $file7 = $this->createFile('My Title Seven', 'My Description', 'myfilename.png', ['tag1', 'tag2'], 'Approved', ['Comment ten', 'Comment two']);

        $response = $this->get($this->adminApiUrl('/file/search?page=1&per_page=10&filter=One'));
        $arrayResponse = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $arrayResponse);
        $files = $arrayResponse['data'];

        $this->assertCount(4, $files);
        $ids = [$file1->id, $file2->id, $file3->id, $file6->id];

        foreach($files as $returnedFile) {
            $this->assertEquals(array_shift($ids), $returnedFile['id']);
        }
    }

    /** @test */
    public function it_only_allows_valid_filter_on_values(){
        // TODO Use data provider
    }

    /** @test */
    public function the_fields_to_filter_on_can_be_controlled(){
        $this->bypassAuthorization();

        $file1 = $this->createFile('My Title One', 'My Description AA', 'myfilename.png', ['tag1', 'tag2'], 'Approved', ['Comment ten', 'Comment two']);
        $file2 = $this->createFile('My Title Two', 'My Description One', 'myfilename.png', ['tag1', 'tag2'], 'Approved', ['Comment ten', 'Comment two']);
        $file3 = $this->createFile('My Title Three', 'My Description', 'myfilename One test.png', ['tag1', 'tag2'], 'Approved', ['Comment ten', 'Comment two']);
        $file4 = $this->createFile('My Title Four', 'My Description', 'myfilename.png', ['tag1', 'tag2'], 'Approved', ['Comment ten', 'Comment two']);
        $file5 = $this->createFile('My Title Five', 'My Description', 'myfilename.png', ['tag1', 'tag2'], 'Approved', ['Comment ten', 'Comment two']);
        $file6 = $this->createFile('My Title Six', 'My Description', 'myfilename.png', ['tag1', 'tag2'], 'Approved', ['Comment One', 'Comment two']);
        $file7 = $this->createFile('My Title Seven', 'My Description', 'myfilename.png', ['tag1', 'tag2'], 'Approved', ['Comment ten', 'Comment two']);

        $response = $this->get($this->adminApiUrl('/file/search?page=1&per_page=10&filter=One&filterOn[]=title&filterOn[]=description&filterOn[]=comments'));

        $arrayResponse = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $arrayResponse);
        $files = $arrayResponse['data'];

        $this->assertCount(4, $files);
        $ids = [$file1->id, $file2->id, $file3->id, $file6->id];

        foreach($files as $returnedFile) {
            $this->assertEquals(array_shift($ids), $returnedFile['id']);
        }
    }

    /** @test */
    public function it_paginates_the_results(){

    }

    private function createFile(string $title, string $description, string $filename, array $tags, string $status, array $comments)
    {
        $file = factory(File::class)->create([
            'title' => $title,
            'description' => $description,
            'filename' => $filename,
            'tags' => $tags,
            'module_instance_id' => $this->getModuleInstance()->id()
        ]);
        factory(FileStatus::class)->create(['status' => $status, 'file_id' => $file->id]);
        foreach($comments as $comment) {
            factory(Comment::class)->create(['comment' => $comment, 'file_id' => $file->id]);
        }
        $file->searchable();
        return $file;
    }

}
