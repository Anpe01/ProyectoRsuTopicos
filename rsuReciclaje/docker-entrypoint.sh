#!/bin/bash
set -e

echo "🚀 Iniciando aplicación Laravel..."

# Esperar a que MySQL esté listo
echo "⏳ Esperando a MySQL..."
for i in {1..30}; do
    if nc -z mysql 3306 2>/dev/null; then
        echo "✅ MySQL está disponible"
        break
    fi
    echo "MySQL no está disponible aún - esperando... ($i/30)"
    sleep 2
done

# Verificar conexión a MySQL
if ! nc -z mysql 3306 2>/dev/null; then
    echo "⚠️  Advertencia: No se pudo conectar a MySQL, pero continuando..."
fi

# Limpiar cachés
echo "🧹 Limpiando cachés..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Generar clave de aplicación si no existe
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        echo "📝 Creando archivo .env desde .env.example..."
        cp .env.example .env
    else
        echo "📝 Creando archivo .env básico..."
        cat > .env <<EOF
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=rsu_reciclaje
DB_USERNAME=rsu_user
DB_PASSWORD=rsu_password
EOF
    fi
    php artisan key:generate || true
fi

# Actualizar variables de entorno en .env si están definidas
echo "📝 Actualizando variables de entorno en .env..."
if [ ! -z "$DB_HOST" ]; then
    sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
fi
if [ ! -z "$DB_DATABASE" ]; then
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" .env
fi
if [ ! -z "$DB_USERNAME" ]; then
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/" .env
fi
if [ ! -z "$DB_PASSWORD" ]; then
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
fi
if [ ! -z "$DB_CONNECTION" ]; then
    sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=$DB_CONNECTION/" .env
fi

# Configurar permisos
echo "🔐 Configurando permisos..."
chown -R www-data:www-data /var/www/html/storage || true
chown -R www-data:www-data /var/www/html/bootstrap/cache || true
chmod -R 775 /var/www/html/storage || true
chmod -R 775 /var/www/html/bootstrap/cache || true

# Ejecutar migraciones (solo si la base de datos está disponible)
echo "📦 Intentando ejecutar migraciones..."
php artisan migrate --force || echo "⚠️  No se pudieron ejecutar las migraciones (puede que la BD no esté lista aún)"

echo "✅ Aplicación lista!"

# Ejecutar PHP-FPM
exec "$@"


