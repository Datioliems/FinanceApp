<?php
// ============================================================
// MODEL — app/Models/Transaction.php
// ============================================================
// CHECKPOINT C: Bỏ userId — đúng scope đề bài
// Template Method Pattern giữ nguyên hoàn toàn
// ============================================================

namespace App\Models;

abstract class Transaction
{
    protected ?int   $id        = null;
    protected int    $categoryId;
    protected float  $amount;
    protected string $note;
    protected string $transDate;

    public function __construct(
        int    $categoryId,
        float  $amount,
        string $transDate,
        string $note = ''
    ) {
        $this->categoryId = $categoryId;
        $this->amount     = $amount;
        $this->transDate  = $transDate;
        $this->note       = $note;
    }

    // ── Template Method — KHÔNG ai override được thứ tự ─────
    final public function process(): void
    {
        $this->validate();  // Bước 1: kiểm tra dữ liệu
        $this->save();      // Bước 2: lưu vào DB
        $this->notify();    // Bước 3: xử lý sau khi lưu
    }

    abstract protected function validate(): void;
    abstract protected function save(): void;
    abstract protected function notify(): void;
    abstract public function getType(): string;

    public function getId(): ?int          { return $this->id; }
    public function getCategoryId(): int   { return $this->categoryId; }
    public function getAmount(): float     { return $this->amount; }
    public function getTransDate(): string { return $this->transDate; }
    public function getNote(): string      { return $this->note; }

    public function toArray(): array
    {
        return [
            'category_id' => $this->categoryId,
            'type'        => $this->getType(),
            'amount'      => $this->amount,
            'note'        => $this->note,
            'trans_date'  => $this->transDate,
        ];
    }
}
