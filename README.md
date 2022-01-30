# Configurações Necessárias
No diretorio tem uma pasta chamada efinance, que é a api em PHP para interação com o MySql, crie no seu servidor o caminho localhost/efinance/ caso use um nome diferente é preciso trocar o caminho da api no arquivo ./src/boot/axios.ts

No cdiretorio ./efinance/scripts MySql/scripts.sql se encontra a criação do banco e as tabelas utilizas

No cdiretorio ./efinance/conn/ConexaoMysql.php você deve trocar a conexão com o MySql

# Quasar App (efinance)

A Quasar Framework app

## Install the dependencies
```bash
yarn
```

### Start the app in development mode (hot-code reloading, error reporting, etc.)
```bash
quasar dev
```

### Lint the files
```bash
yarn run lint
```

### Build the app for production
```bash
quasar build
```

### Customize the configuration
See [Configuring quasar.conf.js](https://quasar.dev/quasar-cli/quasar-conf-js).
