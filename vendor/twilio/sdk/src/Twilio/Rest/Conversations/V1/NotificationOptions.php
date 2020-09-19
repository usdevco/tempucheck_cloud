<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\Conversations\V1;

use Twilio\Options;
use Twilio\Values;

/**
 * PLEASE NOTE that this class contains beta products that are subject to change. Use them with caution.
 */
abstract class NotificationOptions {
    /**
     * @param bool $logEnabled Weather the notification logging is enabled.
     * @param bool $newMessageEnabled Whether to send a notification when a new
     *                                message is added to a conversation.
     * @param string $newMessageTemplate The template to use to create the
     *                                   notification text displayed when a new
     *                                   message is added to a conversation.
     * @param string $newMessageSound The name of the sound to play when a new
     *                                message is added to a conversation.
     * @param bool $newMessageBadgeCountEnabled Whether the new message badge is
     *                                          enabled.
     * @param bool $addedToConversationEnabled Whether to send a notification when
     *                                         a participant is added to a
     *                                         conversation.
     * @param string $addedToConversationTemplate The template to use to create the
     *                                            notification text displayed when
     *                                            a participant is added to a
     *                                            conversation.
     * @param string $addedToConversationSound The name of the sound to play when a
     *                                         participant is added to a
     *                                         conversation.
     * @param bool $removedFromConversationEnabled Whether to send a notification
     *                                             to a user when they are removed
     *                                             from a conversation.
     * @param string $removedFromConversationTemplate The template to use to create
     *                                                the notification text
     *                                                displayed to a user when they
     *                                                are removed.
     * @param string $removedFromConversationSound The name of the sound to play to
     *                                             a user when they are removed
     *                                             from a conversation.
     * @return UpdateNotificationOptions Options builder
     */
    public static function update(bool $logEnabled = Values::NONE, bool $newMessageEnabled = Values::NONE, string $newMessageTemplate = Values::NONE, string $newMessageSound = Values::NONE, bool $newMessageBadgeCountEnabled = Values::NONE, bool $addedToConversationEnabled = Values::NONE, string $addedToConversationTemplate = Values::NONE, string $addedToConversationSound = Values::NONE, bool $removedFromConversationEnabled = Values::NONE, string $removedFromConversationTemplate = Values::NONE, string $removedFromConversationSound = Values::NONE): UpdateNotificationOptions {
        return new UpdateNotificationOptions($logEnabled, $newMessageEnabled, $newMessageTemplate, $newMessageSound, $newMessageBadgeCountEnabled, $addedToConversationEnabled, $addedToConversationTemplate, $addedToConversationSound, $removedFromConversationEnabled, $removedFromConversationTemplate, $removedFromConversationSound);
    }
}

class UpdateNotificationOptions extends Options {
    /**
     * @param bool $logEnabled Weather the notification logging is enabled.
     * @param bool $newMessageEnabled Whether to send a notification when a new
     *                                message is added to a conversation.
     * @param string $newMessageTemplate The template to use to create the
     *                                   notification text displayed when a new
     *                                   message is added to a conversation.
     * @param string $newMessageSound The name of the sound to play when a new
     *                                message is added to a conversation.
     * @param bool $newMessageBadgeCountEnabled Whether the new message badge is
     *                                          enabled.
     * @param bool $addedToConversationEnabled Whether to send a notification when
     *                                         a participant is added to a
     *                                         conversation.
     * @param string $addedToConversationTemplate The template to use to create the
     *                                            notification text displayed when
     *                                            a participant is added to a
     *                                            conversation.
     * @param string $addedToConversationSound The name of the sound to play when a
     *                                         participant is added to a
     *                                         conversation.
     * @param bool $removedFromConversationEnabled Whether to send a notification
     *                                             to a user when they are removed
     *                                             from a conversation.
     * @param string $removedFromConversationTemplate The template to use to create
     *                                                the notification text
     *                                                displayed to a user when they
     *                                                are removed.
     * @param string $removedFromConversationSound The name of the sound to play to
     *                                             a user when they are removed
     *                                             from a conversation.
     */
    public function __construct(bool $logEnabled = Values::NONE, bool $newMessageEnabled = Values::NONE, string $newMessageTemplate = Values::NONE, string $newMessageSound = Values::NONE, bool $newMessageBadgeCountEnabled = Values::NONE, bool $addedToConversationEnabled = Values::NONE, string $addedToConversationTemplate = Values::NONE, string $addedToConversationSound = Values::NONE, bool $removedFromConversationEnabled = Values::NONE, string $removedFromConversationTemplate = Values::NONE, string $removedFromConversationSound = Values::NONE) {
        $this->options['logEnabled'] = $logEnabled;
        $this->options['newMessageEnabled'] = $newMessageEnabled;
        $this->options['newMessageTemplate'] = $newMessageTemplate;
        $this->options['newMessageSound'] = $newMessageSound;
        $this->options['newMessageBadgeCountEnabled'] = $newMessageBadgeCountEnabled;
        $this->options['addedToConversationEnabled'] = $addedToConversationEnabled;
        $this->options['addedToConversationTemplate'] = $addedToConversationTemplate;
        $this->options['addedToConversationSound'] = $addedToConversationSound;
        $this->options['removedFromConversationEnabled'] = $removedFromConversationEnabled;
        $this->options['removedFromConversationTemplate'] = $removedFromConversationTemplate;
        $this->options['removedFromConversationSound'] = $removedFromConversationSound;
    }

    /**
     * Weather the notification logging is enabled.
     *
     * @param bool $logEnabled Weather the notification logging is enabled.
     * @return $this Fluent Builder
     */
    public function setLogEnabled(bool $logEnabled): self {
        $this->options['logEnabled'] = $logEnabled;
        return $this;
    }

    /**
     * Whether to send a notification when a new message is added to a conversation. The default is `false`.
     *
     * @param bool $newMessageEnabled Whether to send a notification when a new
     *                                message is added to a conversation.
     * @return $this Fluent Builder
     */
    public function setNewMessageEnabled(bool $newMessageEnabled): self {
        $this->options['newMessageEnabled'] = $newMessageEnabled;
        return $this;
    }

    /**
     * The template to use to create the notification text displayed when a new message is added to a conversation and `new_message.enabled` is `true`.
     *
     * @param string $newMessageTemplate The template to use to create the
     *                                   notification text displayed when a new
     *                                   message is added to a conversation.
     * @return $this Fluent Builder
     */
    public function setNewMessageTemplate(string $newMessageTemplate): self {
        $this->options['newMessageTemplate'] = $newMessageTemplate;
        return $this;
    }

    /**
     * The name of the sound to play when a new message is added to a conversation and `new_message.enabled` is `true`.
     *
     * @param string $newMessageSound The name of the sound to play when a new
     *                                message is added to a conversation.
     * @return $this Fluent Builder
     */
    public function setNewMessageSound(string $newMessageSound): self {
        $this->options['newMessageSound'] = $newMessageSound;
        return $this;
    }

    /**
     * Whether the new message badge is enabled. The default is `false`.
     *
     * @param bool $newMessageBadgeCountEnabled Whether the new message badge is
     *                                          enabled.
     * @return $this Fluent Builder
     */
    public function setNewMessageBadgeCountEnabled(bool $newMessageBadgeCountEnabled): self {
        $this->options['newMessageBadgeCountEnabled'] = $newMessageBadgeCountEnabled;
        return $this;
    }

    /**
     * Whether to send a notification when a participant is added to a conversation. The default is `false`.
     *
     * @param bool $addedToConversationEnabled Whether to send a notification when
     *                                         a participant is added to a
     *                                         conversation.
     * @return $this Fluent Builder
     */
    public function setAddedToConversationEnabled(bool $addedToConversationEnabled): self {
        $this->options['addedToConversationEnabled'] = $addedToConversationEnabled;
        return $this;
    }

    /**
     * The template to use to create the notification text displayed when a participant is added to a conversation and `added_to_conversation.enabled` is `true`.
     *
     * @param string $addedToConversationTemplate The template to use to create the
     *                                            notification text displayed when
     *                                            a participant is added to a
     *                                            conversation.
     * @return $this Fluent Builder
     */
    public function setAddedToConversationTemplate(string $addedToConversationTemplate): self {
        $this->options['addedToConversationTemplate'] = $addedToConversationTemplate;
        return $this;
    }

    /**
     * The name of the sound to play when a participant is added to a conversation and `added_to_conversation.enabled` is `true`.
     *
     * @param string $addedToConversationSound The name of the sound to play when a
     *                                         participant is added to a
     *                                         conversation.
     * @return $this Fluent Builder
     */
    public function setAddedToConversationSound(string $addedToConversationSound): self {
        $this->options['addedToConversationSound'] = $addedToConversationSound;
        return $this;
    }

    /**
     * Whether to send a notification to a user when they are removed from a conversation. The default is `false`.
     *
     * @param bool $removedFromConversationEnabled Whether to send a notification
     *                                             to a user when they are removed
     *                                             from a conversation.
     * @return $this Fluent Builder
     */
    public function setRemovedFromConversationEnabled(bool $removedFromConversationEnabled): self {
        $this->options['removedFromConversationEnabled'] = $removedFromConversationEnabled;
        return $this;
    }

    /**
     * The template to use to create the notification text displayed to a user when they are removed from a conversation and `removed_from_conversation.enabled` is `true`.
     *
     * @param string $removedFromConversationTemplate The template to use to create
     *                                                the notification text
     *                                                displayed to a user when they
     *                                                are removed.
     * @return $this Fluent Builder
     */
    public function setRemovedFromConversationTemplate(string $removedFromConversationTemplate): self {
        $this->options['removedFromConversationTemplate'] = $removedFromConversationTemplate;
        return $this;
    }

    /**
     * The name of the sound to play to a user when they are removed from a conversation and `removed_from_conversation.enabled` is `true`.
     *
     * @param string $removedFromConversationSound The name of the sound to play to
     *                                             a user when they are removed
     *                                             from a conversation.
     * @return $this Fluent Builder
     */
    public function setRemovedFromConversationSound(string $removedFromConversationSound): self {
        $this->options['removedFromConversationSound'] = $removedFromConversationSound;
        return $this;
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string {
        $options = \http_build_query(Values::of($this->options), '', ' ');
        return '[Twilio.Conversations.V1.UpdateNotificationOptions ' . $options . ']';
    }
}