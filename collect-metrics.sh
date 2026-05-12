#!/bin/bash
echo "=== METRICS COLLECTION ==="
echo ""

echo "📊 Payments in database:"
docker compose exec postgres psql -U payment_user -d payment_gym -t -c "SELECT COUNT(*) FROM payments;"

echo ""
echo "📊 Payments by status:"
docker compose exec postgres psql -U payment_user -d payment_gym -c "SELECT status, COUNT(*) FROM payments GROUP BY status;"

echo ""
echo "📊 RabbitMQ Queue status:"
curl -s http://localhost:15673/api/queues | jq '.[] | {name: .name, messages: .messages, consumers: .consumers, messages_ready: .messages_ready}'

echo ""
echo "📊 Worker log (last 5 lines):"
docker compose logs worker --tail=5

echo ""
echo "📊 Container stats:"
docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}"
