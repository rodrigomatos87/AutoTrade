#!/bin/bash

# Coleta informações sobre a RAM e CPU
total_memory=$(grep MemTotal /proc/meminfo | awk '{print $2}')
total_memory=$((total_memory / 1024)) # Converte para MB
total_cores=$(nproc)

# Calcula os valores de configuração
memory_limit=$((total_memory * 60 / 100))
innodb_buffer_pool_size=$((memory_limit * 80 / 100))
sort_buffer_size=$((memory_limit * 2 / 100))
join_buffer_size=$((memory_limit * 2 / 100))
tmp_table_size=$((memory_limit * 20 / 100))
max_heap_table_size=$tmp_table_size

# Verifica se há apenas um core de processamento e ajusta as instâncias do buffer pool
if [ $total_cores -eq 1 ]; then
    innodb_buffer_pool_instances=1
else
    innodb_buffer_pool_instances=$((total_cores * 60 / 100))
fi

# Calcula o valor de max_connections com base em uma estimativa conservadora
max_connections=$(((memory_limit * 1024) / 1024)) # Estimativa de 1MB por conexão

# Define o número de threads de leitura e escrita do InnoDB
io_threads=$((total_cores / 2))
io_threads=$((io_threads < 8 ? 8 : io_threads)) # Limita o mínimo a 8
io_threads=$((io_threads > 64 ? 64 : io_threads)) # Limita o máximo a 64

# Faz um backup das configurações antigas
cp /etc/mysql/mariadb.conf.d/50-server.cnf /etc/mysql/mariadb.conf.d/50-server.cnf-bkp

# Cria o arquivo de configuração otimizado
cat > /etc/mysql/mariadb.conf.d/50-server.cnf <<EOL
[server]

[mysqld]
user = mysql
pid-file = /run/mysqld/mysqld.pid
basedir = /usr
datadir = /var/lib/mysql
tmpdir = /tmp
lc-messages-dir = /usr/share/mysql
lc-messages = en_US
skip-external-locking

performance_schema = off

skip-name-resolve
bind-address = 0.0.0.0
key_buffer_size = 128M
max_allowed_packet = 64M
thread_cache_size = 15121
table_cache = 6500
myisam_sort_buffer_size = 64M
join_buffer_size = ${join_buffer_size}M
read_buffer_size = 4M
sort_buffer_size = ${sort_buffer_size}M
wait_timeout = 3600
connect_timeout = 1000
tmp_table_size = ${tmp_table_size}M
max_heap_table_size = ${max_heap_table_size}M
max_connect_errors = 1000
read_rnd_buffer_size = 300000
bulk_insert_buffer_size = 512M
query_cache_limit = 512M
query_cache_size = 512M
query_cache_type = 1
query_prealloc_size = 65536
query_alloc_block_size = 131072
innodb_buffer_pool_size = ${innodb_buffer_pool_size}M
innodb_buffer_pool_instances = ${innodb_buffer_pool_instances}
innodb_log_file_size = 256M
innodb_log_buffer_size = 16M
innodb_flush_method = O_DIRECT
innodb_flush_log_at_trx_commit = 2
innodb_read_io_threads = ${io_threads}
innodb_write_io_threads = ${io_threads}
innodb_io_capacity = 2000
innodb_io_capacity_max = 4000
query_cache_type = 0
query_cache_size = 0

open_files_limit = 8192

expire_logs_days = 10

character-set-server = utf8mb4
collation-server = utf8mb4_general_ci

# Ajustes gerais
max_connections = ${max_connections}

# Ajustes de segurança
local_infile = 0
symbolic_links = 0

# Ajustes de desempenho adicionais
thread_handling = pool-of-threads
innodb_adaptive_hash_index = 0
innodb_adaptive_flushing = 1
innodb_doublewrite = 0
innodb_file_per_table = 1
innodb_flush_neighbors = 0
innodb_lru_scan_depth = 1024
innodb_purge_threads = 2
innodb_stats_on_metadata = 0
innodb_autoinc_lock_mode = 2

[embedded]
EOL

systemctl restart mariadb