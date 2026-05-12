CREATE TABLE IF NOT EXISTS payment_analytics (
    merchant_id String,
    amount Float64,
    currency String,
    status String,
    payment_id String,
    created_at DateTime,
    day_bucket Date
) ENGINE = MergeTree()
ORDER BY (day_bucket, merchant_id);

CREATE TABLE IF NOT EXISTS payment_hourly_stats (
    hour_bucket DateTime,
    merchant_id String,
    total_amount Float64,
    payment_count UInt64
) ENGINE = SummingMergeTree()
ORDER BY (hour_bucket, merchant_id);
