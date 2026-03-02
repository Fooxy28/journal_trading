<?php

declare(strict_types=1);

final class AjaxController
{
    public function __construct(private readonly TradingService $service)
    {
    }

    public function handle(string $action, array $get, array $post): never
    {
        try {
            switch ($action) {
                case 'get_dropdowns':
                    JsonResponse::success(['data' => $this->service->getDropdowns()]);

                case 'add_account':
                    $id = $this->service->addAccount((string) ($post['account_name'] ?? ''));
                    JsonResponse::success(['message' => 'Akun berhasil ditambahkan.', 'id' => $id]);

                case 'delete_account':
                    $this->service->deleteAccount((int) ($post['account_id'] ?? 0));
                    JsonResponse::success(['message' => 'Akun berhasil dihapus.']);

                case 'add_pair':
                    $id = $this->service->addPair((string) ($post['pair_code'] ?? ''));
                    JsonResponse::success(['message' => 'Pair berhasil ditambahkan.', 'id' => $id]);

                case 'add_strategy':
                    $id = $this->service->addStrategy((string) ($post['strategy_name'] ?? ''));
                    JsonResponse::success(['message' => 'Strategi berhasil ditambahkan.', 'id' => $id]);

                case 'adjust_balance':
                    $this->service->adjustBalance(
                        (int) ($post['account_id'] ?? 0),
                        (string) ($post['type'] ?? ''),
                        (float) ($post['amount'] ?? 0),
                        !empty($post['note']) ? (string) $post['note'] : null
                    );
                    JsonResponse::success(['message' => 'Balance berhasil diperbarui!']);

                case 'save_trade':
                    $this->service->saveTrade($post);
                    JsonResponse::success(['message' => 'Jurnal trading berhasil disimpan!']);

                case 'get_trades':
                    $trades = $this->service->getTrades(
                        (int) ($get['account_id'] ?? 0),
                        (int) ($get['page'] ?? 1)
                    );
                    JsonResponse::success(['data' => $trades]);

                case 'get_analytics':
                    $analytics = $this->service->getAnalytics((int) ($get['account_id'] ?? 0));
                    JsonResponse::success(['data' => $analytics]);

                case 'delete_trade':
                    $this->service->deleteTrade(
                        (int) ($post['trade_id'] ?? 0),
                        (int) ($post['account_id'] ?? 0)
                    );
                    JsonResponse::success(['message' => 'Trade berhasil dihapus.']);

                default:
                    JsonResponse::error('Aksi tidak dikenali.', 400);
            }
        } catch (InvalidArgumentException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        } catch (PDOException $exception) {
            if ((string) $exception->getCode() === '23000') {
                JsonResponse::error('Data tersebut sudah terdaftar/duplikat!', 409);
            }

            error_log('Database error on AjaxController: ' . $exception->getMessage());
            JsonResponse::error('Terjadi kesalahan database. Silakan coba lagi.', 500);
        } catch (Throwable $throwable) {
            error_log('Unhandled error on AjaxController: ' . $throwable->getMessage());
            JsonResponse::error('Terjadi kesalahan server.', 500);
        }
    }
}
