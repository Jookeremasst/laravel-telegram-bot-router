<?php

namespace ReyhanTeam\TelegramBotRouter;

use Illuminate\Support\Str;
use stdClass;

class TelegramUpdate
{
    
    protected stdClass $update;

    public ?array $matches = null; // برای نگهداری نتایج RegExp

    public function __construct($update)
    {
        $this->update = is_array($update) ? (object) $update : $update;
    }

    /**
     * Dynamically access properties of the update object.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->recursiveGet($this->update, $key);
    }

    /**
     * Recursively access nested properties.
     *
     * @param  mixed  $object
     * @return mixed
     */
    protected function recursiveGet($object, string $key)
    {
        if (is_object($object) && property_exists($object, $key)) {

            $value = $object->{$key};

            if (is_object($value) || is_array($value)) {
                return new static($value);
            }

            return $value;
        }

        if (is_object($object)) {
            foreach ($object as $prop => $value) {

                if (Str::snake($prop) === $key) {

                    if (is_object($value) || is_array($value)) {
                        return new static($value);
                    }

                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * Check if the update has a specific property.
     *
     * @param  string  $key
     */
    public function __isset($key): bool
    {
        return $this->recursiveIsset($this->update, $key);
    }

    /**
     * Recursively check if a property exists.
     *
     * @param  mixed  $object
     */
    protected function recursiveIsset($object, string $key): bool
    {
        if (is_object($object) && property_exists($object, $key)) {
            return true;
        } elseif (is_object($object)) {
            foreach ($object as $prop => $value) {
                if (Str::snake($prop) === $key) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the original update object.
     */
    public function originalUpdate(): stdClass
    {
        return $this->update;
    }

    /**
     * Alias for message->chat->id.
     *
     * @return mixed
     */

    public function chatId()
    {
        return $this->message->chat->id
            ?? $this->callback_query->message->chat->id
            ?? $this->edited_message->chat->id
            ?? $this->channel_post->chat->id
            ?? null;
    }

    public function userId()
    {
        return $this->message->from->id
            ?? $this->callback_query->from->id
            ?? null;
    }

    public function messageId()
    {
        return $this->message->message_id
            ?? $this->callback_query->message->message_id
            ?? $this->edited_message->message_id
            ?? null;
    }

    /**
     * Alias for message->text.
     */
    public function text()
    {
        return $this->message->text
            ?? $this->callback_query->data
            ?? null;
    }

    /**
     * Alias for callback_query->data.
     */
    public function callbackQueryData(): ?string
    {
        return $this->callback_query->data ?? null;
    }

    public static function fromArray($data)
    {
        return new self($data);
    }
}
