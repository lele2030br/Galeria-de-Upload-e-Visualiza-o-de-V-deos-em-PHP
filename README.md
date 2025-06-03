# 📹 Galeria de Upload e Visualização de Vídeos em PHP

Um site simples e responsivo para upload, listagem e visualização de vídeos desenvolvido em PHP puro, **sem necessidade de cadastro de usuários ou banco de dados**. Os vídeos são armazenados em uma pasta local, tornando o sistema fácil de instalar e usar em qualquer servidor com suporte a PHP.

## ✨ Funcionalidades

- **Upload de vídeos** (`.mp4`, `.webm`, `.ogv`) de até 100MB por arquivo, com barra de progresso animada.
- **Galeria moderna e responsiva** com visualização em grid dos vídeos enviados.
- **Player modal elegante**: os vídeos podem ser assistidos em destaque, sem sair da galeria.
- **Listagem dinâmica**: nome do arquivo e tamanho exibidos, vídeos mais recentes primeiro.
- **Interface bonita e responsiva** para desktop e mobile.
- **Validação e segurança básica**: só permite vídeos e limita o tamanho do arquivo.
- **Sem banco de dados e sem cadastro**: apenas arquivos no servidor!

## 🖼️ Demonstração

![Exemplo da interface da galeria de vídeos](docs/demo.png)

## 🚀 Como usar

1. **Requisitos**
   - PHP 7.0 ou superior
   - Servidor web com permissão de escrita na pasta do projeto

2. **Instalação**
   - Baixe ou clone este repositório em seu servidor:
     ```bash
     git clone https://github.com/seuusuario/seurepo.git
     ```
   - (Opcional) Crie uma pasta chamada `uploads` no mesmo diretório do arquivo `index.php` e garanta permissão de escrita. O sistema também cria a pasta automaticamente se ela não existir.

3. **Executando**
   - Acesse o diretório do projeto pelo navegador (exemplo: `http://localhost/seurepo/`).
   - Envie seus vídeos e aproveite a galeria!

## ⚙️ Estrutura dos arquivos

```
/
├── index.php      # Página principal (upload, galeria e player)
├── uploads/       # Pasta onde os vídeos enviados são armazenados
└── README.md
```

## 🔒 Segurança

- Apenas arquivos de vídeo são permitidos.
- Limite de tamanho de 100MB por vídeo.
- As extensões e os tipos MIME são validados no upload.
- Os arquivos são renomeados de forma única para evitar conflitos.

## 💡 Personalização

Você pode adaptar facilmente a galeria para:
- Permitir outros formatos de vídeo.
- Alterar o limite de tamanho.
- Adicionar autenticação (se desejar).
- Trocar cores e estilos no CSS embutido.

## 📝 Licença

Este projeto está sob a licença MIT. Fique à vontade para usar, modificar e compartilhar!

---

Feito com 💜 em PHP!