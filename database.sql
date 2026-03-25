-- SQL Dump AWTecnology - AWT-Sales-Manager
-- 16 Tabelas Estruturadas

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "-03:00";

-- 1. Usuários e Acesso
CREATE TABLE `usuarios` (
  `idUsuario` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `senha` varchar(255) NOT NULL,
  `nivel` enum('admin','user') DEFAULT 'user',
  PRIMARY KEY (`idUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Cadastros Base
CREATE TABLE `categoria` (
  `idCategoria` int(11) NOT NULL AUTO_INCREMENT,
  `nomeCategoria` varchar(100) NOT NULL,
  PRIMARY KEY (`idCategoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cliente` (
  `idCliente` int(11) NOT NULL AUTO_INCREMENT,
  `Pnome` varchar(50) NOT NULL,
  `Sobrenome` varchar(50) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idCliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `fornecedor` (
  `idFornecedor` int(11) NOT NULL AUTO_INCREMENT,
  `nomeEmpresa` varchar(150) NOT NULL,
  PRIMARY KEY (`idFornecedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `formapagamento` (
  `idForma` int(11) NOT NULL AUTO_INCREMENT,
  `nomeForma` varchar(50) NOT NULL,
  PRIMARY KEY (`idForma`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Gestão de Produtos e Estoque
CREATE TABLE `produtos` (
  `idProduto` int(11) NOT NULL AUTO_INCREMENT,
  `Descricao` varchar(200) NOT NULL,
  `Valor` decimal(10,2) NOT NULL,
  `estoque` int(11) NOT NULL DEFAULT 0,
  `idCategoria` int(11),
  PRIMARY KEY (`idProduto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `estoque` (`id` int PRIMARY KEY AUTO_INCREMENT, `idProduto` int, `qtd` int, `tipo` varchar(20));
CREATE TABLE `estoque_produtos` (`id` int PRIMARY KEY AUTO_INCREMENT, `idProduto` int, `movimentacao` int);
CREATE TABLE `fornecedor_produtos` (`id` int PRIMARY KEY AUTO_INCREMENT, `idFornecedor` int, `idProduto` int);

-- 4. Pedidos e Vendas (A Lógica Principal)
CREATE TABLE `pedido` (
  `idPedido` int(11) NOT NULL AUTO_INCREMENT,
  `idCliente` int(11) NOT NULL,
  `codigoVenda` varchar(50) NOT NULL,
  `StatusPedido` varchar(50) DEFAULT 'Confirmado',
  `dataPedido` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idPedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `item_pedido` (`id` int PRIMARY KEY AUTO_INCREMENT, `codigoVenda` varchar(50), `idProduto` int, `qtd` int, `valor_unit` decimal(10,2));
CREATE TABLE `pagamento_pedido` (`id` int PRIMARY KEY AUTO_INCREMENT, `codigoVenda` varchar(50), `idForma` int, `valor` decimal(10,2));
CREATE TABLE `relacao_produto_pedido` (`id` int PRIMARY KEY AUTO_INCREMENT, `idPedido` int, `idProduto` int);
CREATE TABLE `vendas` (`idVenda` int PRIMARY KEY AUTO_INCREMENT, `codigoVenda` varchar(50), `total` decimal(10,2), `data` date);

-- 5. Logística e Compras
CREATE TABLE `entrega` (`id` int PRIMARY KEY AUTO_INCREMENT, `codigoVenda` varchar(50), `status` varchar(50));
CREATE TABLE `compra` (`id` int PRIMARY KEY AUTO_INCREMENT, `idFornecedor` int, `valor_total` decimal(10,2), `data` date);

-- Dados Iniciais
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `nivel`) VALUES ('André Waldige', 'admin@awtecnology.com', '123', 'admin');

COMMIT;
