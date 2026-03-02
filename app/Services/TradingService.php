<?php

declare(strict_types=1);

final class TradingService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function getDropdowns(): array
    {
        return [
            'accounts' => $this->pdo->query('SELECT id, account_name FROM accounts ORDER BY id ASC')->fetchAll(),
            'pairs' => $this->pdo->query('SELECT id, pair_code FROM pairs ORDER BY pair_code ASC')->fetchAll(),
            'strategies' => $this->pdo->query('SELECT id, strategy_name FROM strategies ORDER BY strategy_name ASC')->fetchAll(),
        ];
    }

    public function addAccount(string $accountName): int
    {
        $name = trim($accountName);
        if ($name === '') {
            throw new InvalidArgumentException('Nama akun tidak boleh kosong.');
        }

        $stmt = $this->pdo->prepare('INSERT INTO accounts (account_name) VALUES (?)');
        $stmt->execute([$name]);
        $accountId = (int) $this->pdo->lastInsertId();

        $stmtBal = $this->pdo->prepare('INSERT INTO balances (account_id, balance_amount) VALUES (?, 0)');
        $stmtBal->execute([$accountId]);

        return $accountId;
    }

    public function deleteAccount(int $accountId): void
    {
        if ($accountId <= 0) {
            throw new InvalidArgumentException('Akun tidak valid.');
        }

        $stmtCheck = $this->pdo->prepare('SELECT id FROM accounts WHERE id = ? LIMIT 1');
        $stmtCheck->execute([$accountId]);
        if (!$stmtCheck->fetch()) {
            throw new InvalidArgumentException('Akun tidak ditemukan.');
        }

        $this->pdo->prepare('DELETE FROM accounts WHERE id = ?')->execute([$accountId]);
    }

    public function addPair(string $pairCode): int
    {
        $code = strtoupper(trim($pairCode));
        if ($code === '') {
            throw new InvalidArgumentException('Pair tidak boleh kosong.');
        }

        $stmt = $this->pdo->prepare('INSERT INTO pairs (pair_code) VALUES (?)');
        $stmt->execute([$code]);

        return (int) $this->pdo->lastInsertId();
    }

    public function addStrategy(string $strategyName): int
    {
        $name = trim($strategyName);
        if ($name === '') {
            throw new InvalidArgumentException('Nama strategi tidak boleh kosong.');
        }

        $stmt = $this->pdo->prepare('INSERT INTO strategies (strategy_name) VALUES (?)');
        $stmt->execute([$name]);

        return (int) $this->pdo->lastInsertId();
    }

    public function adjustBalance(int $accountId, string $type, float $amount, ?string $note): void
    {
        if ($accountId <= 0) {
            throw new InvalidArgumentException('Akun tidak valid.');
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException('Jumlah uang harus lebih dari 0.');
        }

        if (!in_array($type, ['deposit', 'withdraw', 'adjustment'], true)) {
            throw new InvalidArgumentException('Tipe adjust tidak valid.');
        }

        $stmtBal = $this->pdo->prepare('SELECT balance_amount FROM balances WHERE account_id = ?');
        $stmtBal->execute([$accountId]);
        $currentBalance = (float) ($stmtBal->fetchColumn() ?: 0);

        if ($type === 'withdraw') {
            $newBalance = $currentBalance - $amount;
        } elseif ($type === 'deposit') {
            $newBalance = $currentBalance + $amount;
        } else {
            $newBalance = $amount;
            $amount = $newBalance - $currentBalance;
        }

        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('UPDATE balances SET balance_amount = ? WHERE account_id = ?')
                ->execute([$newBalance, $accountId]);

            $this->pdo->prepare('INSERT INTO balance_logs (account_id, type, amount, note) VALUES (?, ?, ?, ?)')
                ->execute([$accountId, $type, $amount, $note]);

            $this->pdo->commit();
        } catch (Throwable $throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $throwable;
        }
    }

    public function saveTrade(array $payload): void
    {
        $accountId = (int) ($payload['account_id'] ?? 0);
        $tradeDate = (string) ($payload['trade_date'] ?? '');
        $pairId = (int) ($payload['pair_id'] ?? 0);
        $positionType = (string) ($payload['position_type'] ?? '');
        $entryPrice = (float) ($payload['entry_price'] ?? 0);
        $exitPrice = (float) ($payload['exit_price'] ?? 0);
        $rawAmount = (float) ($payload['amount'] ?? 0);
        $lot = (float) ($payload['lot'] ?? 0);
        $tpSlStatus = (string) ($payload['tp_sl_status'] ?? '');

        if ($accountId <= 0 || $pairId <= 0 || $tradeDate === '') {
            throw new InvalidArgumentException('Data trade tidak lengkap.');
        }

        if (!in_array($positionType, ['buy', 'sell'], true)) {
            throw new InvalidArgumentException('Posisi trade tidak valid.');
        }

        if (!in_array($tpSlStatus, ['manual', 'hit'], true)) {
            throw new InvalidArgumentException('TP/SL status tidak valid.');
        }

        $strategyId = !empty($payload['strategy_id']) ? (int) $payload['strategy_id'] : null;
        $note = !empty($payload['note']) ? (string) $payload['note'] : null;
        $analysisLink = !empty($payload['analysis_link']) ? (string) $payload['analysis_link'] : null;

        $amount = abs($rawAmount);
        $isProfit = ($positionType === 'buy' && $exitPrice > $entryPrice)
            || ($positionType === 'sell' && $exitPrice < $entryPrice);
        $tradingStatus = $isProfit ? 'profit' : 'loss';

        if ($tradingStatus === 'loss') {
            $amount = -$amount;
        }

        $stmtBal = $this->pdo->prepare('SELECT balance_amount FROM balances WHERE account_id = ?');
        $stmtBal->execute([$accountId]);
        $currentBalance = (float) ($stmtBal->fetchColumn() ?: 0);

        $netPercent = $currentBalance > 0 ? ($amount / $currentBalance) * 100 : 0;

        $this->pdo->beginTransaction();
        try {
            $sql = 'INSERT INTO trades (account_id, trade_date, pair_id, position_type, entry_price, exit_price, trading_status, amount, net_percent, lot, tp_sl_status, strategy_id, note, analysis_link) \
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $accountId,
                $tradeDate,
                $pairId,
                $positionType,
                $entryPrice,
                $exitPrice,
                $tradingStatus,
                $amount,
                $netPercent,
                $lot,
                $tpSlStatus,
                $strategyId,
                $note,
                $analysisLink,
            ]);

            $tradeId = (int) $this->pdo->lastInsertId();
            $newBalance = $currentBalance + $amount;

            $this->pdo->prepare('UPDATE balances SET balance_amount = ? WHERE account_id = ?')
                ->execute([$newBalance, $accountId]);

            $this->pdo->prepare("INSERT INTO balance_logs (account_id, type, amount, note) VALUES (?, 'trade', ?, ?)")
                ->execute([$accountId, $amount, "Trade ID: {$tradeId}"]);

            $this->pdo->commit();
        } catch (Throwable $throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $throwable;
        }
    }

    public function getTrades(int $accountId, int $page = 1, int $limit = 50): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;

        $sql = "SELECT t.*, p.pair_code, s.strategy_name
                FROM trades t
                LEFT JOIN pairs p ON t.pair_id = p.id
                LEFT JOIN strategies s ON t.strategy_id = s.id
                WHERE t.account_id = ?
                ORDER BY t.trade_date DESC, t.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$accountId]);

        return $stmt->fetchAll();
    }

    public function getAnalytics(int $accountId): array
    {
        $stmtBal = $this->pdo->prepare('SELECT balance_amount FROM balances WHERE account_id = ?');
        $stmtBal->execute([$accountId]);
        $balance = (float) ($stmtBal->fetchColumn() ?: 0);

        $stmtStats = $this->pdo->prepare('
            SELECT
                COUNT(id) AS total_trades,
                SUM(CASE WHEN trading_status = "profit" THEN 1 ELSE 0 END) AS profit_count,
                SUM(CASE WHEN trading_status = "loss" THEN 1 ELSE 0 END) AS loss_count,
                SUM(CASE WHEN trading_status = "profit" THEN amount ELSE 0 END) AS total_profit,
                SUM(CASE WHEN trading_status = "loss" THEN amount ELSE 0 END) AS total_loss
            FROM trades
            WHERE account_id = ?
        ');
        $stmtStats->execute([$accountId]);
        $stats = $stmtStats->fetch() ?: [];

        $stmtLogs = $this->pdo->prepare('
            SELECT
                SUM(CASE WHEN type = "deposit" THEN amount ELSE 0 END) AS total_deposits,
                SUM(CASE WHEN type = "withdraw" THEN amount ELSE 0 END) AS total_withdrawals
            FROM balance_logs
            WHERE account_id = ?
        ');
        $stmtLogs->execute([$accountId]);
        $logs = $stmtLogs->fetch() ?: [];

        $stmtChart = $this->pdo->prepare('
            SELECT chart_date, daily_change
            FROM (
                SELECT DATE(created_at) AS chart_date, SUM(amount) AS daily_change
                FROM balance_logs
                WHERE account_id = ?
                GROUP BY DATE(created_at)
                ORDER BY chart_date DESC
                LIMIT 14
            ) x
            ORDER BY chart_date ASC
        ');
        $stmtChart->execute([$accountId]);
        $chart = $stmtChart->fetchAll() ?: [];

        return [
            'balance' => $balance,
            'stats' => $stats,
            'logs' => $logs,
            'chart' => $chart,
        ];
    }

    public function deleteTrade(int $tradeId, int $accountId): void
    {
        if ($tradeId <= 0 || $accountId <= 0) {
            throw new InvalidArgumentException('Data trade/account tidak valid.');
        }

        $stmtTrade = $this->pdo->prepare('SELECT id, amount FROM trades WHERE id = ? AND account_id = ? LIMIT 1');
        $stmtTrade->execute([$tradeId, $accountId]);
        $trade = $stmtTrade->fetch();

        if (!$trade) {
            throw new InvalidArgumentException('Trade tidak ditemukan.');
        }

        $this->pdo->beginTransaction();
        try {
            $stmtBal = $this->pdo->prepare('SELECT balance_amount FROM balances WHERE account_id = ? FOR UPDATE');
            $stmtBal->execute([$accountId]);
            $currentBalance = (float) ($stmtBal->fetchColumn() ?: 0);

            $tradeAmount = (float) $trade['amount'];
            $newBalance = $currentBalance - $tradeAmount;

            $this->pdo->prepare('DELETE FROM trades WHERE id = ? AND account_id = ?')
                ->execute([$tradeId, $accountId]);

            $this->pdo->prepare('UPDATE balances SET balance_amount = ? WHERE account_id = ?')
                ->execute([$newBalance, $accountId]);

            $this->pdo->prepare("INSERT INTO balance_logs (account_id, type, amount, note) VALUES (?, 'trade', ?, ?)")
                ->execute([$accountId, -$tradeAmount, "Delete Trade ID: {$tradeId}"]);

            $this->pdo->commit();
        } catch (Throwable $throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $throwable;
        }
    }
}
