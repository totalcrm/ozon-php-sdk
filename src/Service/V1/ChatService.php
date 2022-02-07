<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Service\V1;

use SplFileInfo;
use Throwable;
use TotalCRM\OzonApi\Exception\OzonApiException;
use TotalCRM\OzonApi\Service\AbstractService;
use TotalCRM\OzonApi\TypeCaster;
use TotalCRM\OzonApi\Utils\ArrayHelper;

/**
 * Class ChatService
 * @package TotalCRM\OzonApi\Service\V1
 */
class ChatService extends AbstractService
{
    /**
     * Retrieves a list of chats in which a seller participates.
     *
     * @param array $query ['chat_id_list', 'page', 'page_size']
     *
     * @return array
     * @throws Throwable
     * @throws OzonApiException
     */
    public function list(array $query = []): array
    {
        $query = ArrayHelper::pick($query, ['chat_id_list', 'page', 'page_size']);
        $query = TypeCaster::castArr($query, ['page' => 'int', 'page_size' => 'int']);

        return $this->request('POST', '/v1/chat/list', $query ?: '{}');
    }

    /**
     * Retreives message history in a chat.
     *
     * @param string $chatId
     * @param array $query
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     */
    public function history(string $chatId, array $query = []): array
    {
        $query = ArrayHelper::pick($query, ['from_message_id', 'limit']);

        $query['chat_id'] = $chatId;

        return $this->request('POST', '/v1/chat/history', $query);
    }

    /**
     * Sends a message in an existing chat with a customer.
     * @param string $chatId
     * @param string $text
     * @return bool
     * @throws OzonApiException
     * @throws Throwable
     */
    public function sendMessage(string $chatId, string $text): bool
    {
        $arr = [
            'chat_id' => $chatId,
            'text'    => $text,
        ];

        $response = $this->request('POST', '/v1/chat/send/message', $arr);

        return 'success' === $response;
    }

    /**
     * @see https://api-seller.ozon.ru/apiref/en/#t-title_post_sendfile
     * @param string $chatId
     * @param SplFileInfo $file
     * @return bool
     * @throws OzonApiException
     * @throws Throwable
     */
    public function sendFile(string $chatId, SplFileInfo $file): bool
    {
        $arr = [
            'chat_id'        => $chatId,
            'base64_content' => base64_encode(file_get_contents($file->getPathname())),
            'name'           => $file->getBasename(),
        ];
        $response = $this->request('POST', '/v1/chat/send/file', $arr);

        return 'success' === $response;
    }

    /**
     * @see https://api-seller.ozon.ru/apiref/ru/#t-title_post_chatstart
     *
     * @param string $postingNumber
     * @return string Chat ID
     * @throws OzonApiException
     * @throws Throwable
     */
    public function start(string $postingNumber): string
    {
        $arr = [
            'posting_number' => $postingNumber,
        ];

        return $this->request('POST', '/v1/chat/start', $arr)['chat_id'];
    }

    public function updates(string $chatId, string $fromMessageId, int $limit = 100)
    {
        $arr = [
            'chat_id'         => $chatId,
            'from_message_id' => $fromMessageId,
            'limit'           => $limit,
        ];

        return $this->request('POST', '/v1/chat/updates', $arr);
    }
}
