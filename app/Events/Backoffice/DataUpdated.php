<?php

namespace App\Events\Backoffice;

use App\Libraries\CollectionLibrary;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DataUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public static string $crud = 'u';
    public Model $model;
    public int $id;
    public string $title;
    public ?string $memo;
    public Collection $properties;

    /**
     * Create a new event instance.
     *
     * @param Model $model
     * @param int $id
     * @param string $title
     * @param string|null $memo
     */
    public function __construct(Model $model, int $id, string $title, string $memo = null)
    {
        // get updated columns with values
        $changes = array_diff(array_keys($model->getChanges()), [
            'updated_at'
        ]);

        // get the root model
        while (method_exists($model, 'getParentRelation')) {
            $prefix = $model->getMorphClass();

            $model = $model->getParentRelation();
            $id = $model->first()->id;
            $model = $model->getModel();

            $changes = CollectionLibrary::replaceValuesByPrefix(collect($changes), $prefix)->toArray();
        }

        // set properties
        $this->model = $model;
        $this->id = $id;
        $this->title = $title;
        $this->memo = $memo;
        $this->properties = collect([]);
        $this->setData('changes', $changes);
    }

    public function setData($key, $value)
    {
        $this->properties->put($key, $value);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
