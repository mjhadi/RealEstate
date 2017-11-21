-- phpMyAdmin SQL Dump
-- version 4.7.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 21, 2017 at 05:39 AM
-- Server version: 5.6.38
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cp4809_realestate`
--

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `chatId` int(11) NOT NULL,
  `userId1` int(11) NOT NULL,
  `userId2` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` varchar(2000) NOT NULL,
  `imagePath` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `chats`
--

INSERT INTO `chats` (`chatId`, `userId1`, `userId2`, `date`, `name`, `email`, `message`, `imagePath`) VALUES
(3, 4, 2, '0000-00-00 00:00:00', 'hana', 'johnabbott2017@hotmail.com', 'hello laila ', ''),
(4, 4, 2, '2017-11-10 17:36:10', 'hana', 'johnabbott2017@hotmail.com', 'hello laila ', ''),
(5, 4, 2, '2017-11-17 17:04:22', 'laila', 'mhira.ali.hana@gmail.com', 'hello', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `imageId` int(11) NOT NULL,
  `FILE_NAME` varchar(200) NOT NULL,
  `FILE_SIZE` varchar(200) NOT NULL,
  `propertyId` int(11) NOT NULL,
  `FILE_TYPE` varchar(200) NOT NULL,
  `imagePath` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`imageId`, `FILE_NAME`, `FILE_SIZE`, `propertyId`, `FILE_TYPE`, `imagePath`) VALUES
(3, '022552465_496341164085522_1021475831533009845_n.jpg', '46427', 4, 'image/jpeg', '/assets/img/property-1/property1.jpg'),
(4, '022552465_496341164085522_1021475831533009845_n.jpg', '46427', 4, 'image/jpeg', '/uploads/property2.jpg'),
(5, '022552465_496341164085522_1021475831533009845_n.jpg', '46427', 4, 'image/jpeg', '/uploads/property3.jpg'),
(6, '123031570_1686897434695043_1626278781149809261_n.jpg', '71959', 4, 'image/jpeg', '/uploads/property4.jpg'),
(7, '223032658_538895026449444_8165708533946887575_n.jpg', '78425', 4, 'image/jpeg', '/uploads/property3.jpg'),
(8, '0download.png', '3542', 4, 'image/png', '/uploads/property3.jpg'),
(9, '1ejbcon-mdblifecycle.gif', '12756', 4, 'image/gif', '/uploads/property3.jpg'),
(10, '2evo-south-conods-DTLA-CONDOS-LOFTS-For-Sale.jpg', '50811', 4, 'image/jpeg', '/uploads/property3.jpg'),
(11, '0property-5.jpg', '23291', 4, 'image/jpeg', '/uploads/property-5.jpg'),
(12, '1property-6.jpg', '37822', 4, 'image/jpeg', '/uploads/property-6.jpg'),
(13, '2small-proerty-2.jpg', '10889', 4, 'image/jpeg', '/uploads/small-proerty-2.jpg'),
(14, '3small-property-1.jpg', '5656', 4, 'image/jpeg', '/uploads/small-property-1.jpg'),
(15, '4small-property-2.jpg', '10889', 4, 'image/jpeg', '/uploads/small-property-2.jpg'),
(16, '5small-property-3.jpg', '4462', 4, 'image/jpeg', '/uploads/small-property-3.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `newsId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `newsBody` varchar(2000) NOT NULL,
  `newsDate` date NOT NULL,
  `imagePath` varchar(250) DEFAULT NULL,
  `lien` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`newsId`, `userId`, `title`, `newsBody`, `newsDate`, `imagePath`, `lien`) VALUES
(4, 1, 'second news', 'Toronto market will be 5% increase after loan rules..', '2017-11-08', '/uploads/blog1.jpg', 'http://www.cbc.ca/news/canada/british-columbia/value-of-commercial-real-estate-sales-in-b-c-increased-by-47-in-2016-1.4033889'),
(5, 4, 'Value of commercial real estate sales in B.C. increased by 47% in 2016', 'A shortage of land and a growing economy fuelled a 47-per-cent surge in the value of commercial real estate sales across British Columbia\'s Lower Mainland in 2016, says the head of the region\'s real estate board.\r\n\r\nFigures released Monday by the Real Estate Board of Greater Vancouver show sales involving commercial real estate reached nearly $13 billion last year compared with $8.8 billion in 2015.\r\n\r\nThe report also measured a 21 per cent spike in the number of sales involving commercial real estate over the same one-year period.\r\n\r\n\"It\'s really the confidence in the B.C. and Vancouver economy,\" board president Dan Morrison said.\r\n\r\n\"It\'s no surprise that we see the same thing happen with commercial properties as has been happening for residential properties.\"\r\n\r\nRobert Levine, a principal with Avison Young, said the surge was the result of a \"perfect storm.\"\r\n\r\n\"We have a lot of Canadian institutional investors that have felt over the last few years that Vancouver is very highly priced compared to other locations they can go to in Canada and the us,\" he said. \r\n\r\n\"They see these prices and say we should cash out because we think it can\'t really get much better than this.\"\r\n\r\nImpact of foreign buyers tax unknown\r\n\r\nResidential real estate prices have skyrocketed across the Vancouver area in recent years, prompting the B.C. government to introduce a 15 per cent tax on foreign buyers last summer on homes purchased by anyone who isn\'t a citizen or a permanent resident of Canada.\r\n\r\nLast week, the government announced it was tweaking the law retroactively so that foreigners who come to B.C. through the provincial nominee program won\'t have to pay the tax, which also doesn\'t apply to commercial property.\r\n\r\nAsked if the foreign buyers tax has affected commercial real estate sales, Morrison and Levine said it is possible speculators have redirected their investments from residential to commercial properties, but there is no data to back that up.\r\n\r\nProvince to exempt th', '2017-11-15', '/uploads/blog2.jpg', 'http://creastats.crea.ca/natl/index.html'),
(6, 4, 'Canadian home sales edge up again in October', 'HIGHLIGHTS:\r\nNational home sales rose 0.9% from September to October.\r\nActual (not seasonally adjusted) activity stood 4.3% below last October\'s level.\r\nThe number of newly listed homes edged back by 0.8% from September to October.\r\nThe MLS® Home Price Index (HPI) was up 9.7% year-over-year (y-o-y) in October 2017.\r\nThe national average sale price climbed by 5% y-o-y in October.', '2017-11-16', '/uploads/blog1.jpg', 'http://www.cbc.ca/news/canada/british-columbia/value-of-commercial-real-estate-sales-in-b-c-increased-by-47-in-2016-1.4033889'),
(7, 4, 'Canada housing market rebound seen in mid-2018 as sales rise for second month in a row', 'Canadian Real Estate Association said Friday sales through its Multiple Listing Service in September were up 2.1 per cent compared with the previous month. The increase followed a 1.3 per cent increase in August.\r\n\r\nTD Bank senior economist Michael Dolega said unlike the gain in August, that was driven by Toronto, the increase for September was more widespread.\r\n\r\nBut he noted rising interest rates and coming regulatory changes, including a potential new stress test for borrowers with uninsured mortgages, could impinge on the housing market.\r\n\r\n“Having said that, after some near-term weakness, likely to last into mid-2018, activity should begin to rebound thereafter given the fundamentally supported demand related to strong job growth and strengthening wage dynamics,” Dolega wrote in a note to clients.\r\nHome sales in Canada had been slowing this year following changes by the Ontario government aimed at cooling the Toronto market. CREA noted that sales in September were down almost 12 per cent from the record set in March before Ontario announced its housing plan.\r\n\r\nAlso weighing on the real estate market has been rising mortgage rates.\r\n\r\nThe Bank of Canada has raised its key interest rate target twice this year, driving the big bank prime rates and the cost of variable-rate mortgages higher. The cost of new fixed-rate mortgages have also risen as yields on the bond market have also risen.\r\n\r\nMeanwhile, the Office of the Superintendent of Financial Institutions is finalizing new lending guidelines. Among the changes being considered is a requirement that homebuyers who do not require mortgage insurance still have to show they can make their payments if interest rates rise.\r\n\r\nCREA noted that while the September sales results were encouraging, it is too early to tell if it is start of a longer-term trend.\r\n\r\n“Further tightening of federal regulations aimed at cooling housing markets in Toronto and Vancouver risks creating collateral damage in markets elsewhere in Ca', '2017-11-30', '/uploads/blog4.jpg', 'http://business.financialpost.com/real-estate/canadian-home-sales-gain-ground-in-september-but-down-from-year-ago-mark');

-- --------------------------------------------------------

--
-- Table structure for table `passresets`
--

CREATE TABLE `passresets` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `secretToken` varchar(100) NOT NULL,
  `expiryDateTime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `passresets`
--

INSERT INTO `passresets` (`id`, `userId`, `secretToken`, `expiryDateTime`) VALUES
(1, 1, 'LoEJqufXpoiDOaNLfJR1G7l6JayKSrBEPgngKDd91wNPHBAWks', '2017-11-08 17:34:01');

-- --------------------------------------------------------

--
-- Table structure for table `property`
--

CREATE TABLE `property` (
  `propertyId` int(11) NOT NULL,
  `propertyType` enum('Appartement','Condos','House','Villa','Store') NOT NULL DEFAULT 'Appartement',
  `userId` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `beds` int(11) NOT NULL,
  `baths` int(11) NOT NULL,
  `price` float NOT NULL,
  `squreFeet` varchar(50) NOT NULL,
  `imagePath` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `property`
--

INSERT INTO `property` (`propertyId`, `propertyType`, `userId`, `latitude`, `longitude`, `beds`, `baths`, `price`, `squreFeet`, `imagePath`) VALUES
(1, 'Appartement', 4, '45.54494000', '-73.69644400', 2, 1, 789.36, '20.36', '/uploads/property1.jpg'),
(3, 'Appartement', 1, '45.54494000', '-73.69644400', 3, 4, 60, '500000', '/uploads/property2.jpg'),
(4, 'Appartement', 4, '45.53292200', '-73.69230200', 2, 1, 256.36, '250.23', '/uploads/property3.jpg'),
(5, 'Appartement', 4, '45.53000000', '-73.61000000', 2, 1, 456.36, '50.36', '/uploads/property4.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userId` int(11) NOT NULL,
  `userRole` enum('admin','Buyer','Seller') NOT NULL DEFAULT 'Buyer',
  `socialId` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `address` varchar(100) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  `pathImage` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userId`, `userRole`, `socialId`, `name`, `address`, `phone`, `email`, `password`, `pathImage`) VALUES
(1, 'admin', '', 'MJ Hadi', '3488 Cote des Neiges', '514597049', 'mj_hadi@yahoo.com', 'Nupower5775', NULL),
(2, 'admin', '', 'Leila Rizvandi', '3488 Cote des Neiges', '5145197049', 'l_rizvandi@yahoo.com', '$2y$10$qQEaRknMh0H.l8DgdIpa4eU.g4oBxokBlHPAgTApnzs', NULL),
(4, 'admin', '', 'Hana', '2690 rue victor doree', '5142960249', 'johnabbott2017@hotmail.com', '$2y$10$Iny8SrQbZAyJPfcO/EX.AOWdhtxPCHxLPzoHxqvwxSY', '/uploads/download.png'),
(5, 'Buyer', '', 'Jerry', '1388 rue viel H3M 1E8, Montreal', '589632145', 'jerry@gmail.com', '$2y$10$XTMdj0FPZc/sNO9/FvAOf.P2Vjl6X/FoKuM4FRfOh2L', NULL),
(6, 'Seller', '', 'Sami', '12200 rue attelier', '43897562369', 'sami@johnabbott.ca', '$2y$10$2pkfjhywsLTSRHD/DkBfJuejbzOvriWK2Woj8Efp8UO', NULL),
(7, 'Seller', '', 'Salma', '1236 rue decarie', '5142365478', 'salma@videotron.ca', '$2y$10$6ee.rAU76nxMD.H.8ximtuHlXdc3vWb5xSr0uMnK8al', NULL),
(8, 'Seller', '', 'Hadi', '4523 rue decarie', '145236987', 'hadi@ipd.ca', '$2y$10$lj8nSJ5fBrd3nD11UNwGkORjp4uyxZ/csXdlyIvsbQp', NULL),
(9, 'Seller', '', 'Amine', '2690 rue victor dore', '5142960249', 'amine@gmail.com', '$2y$10$OMNs50KQhgz7Q3AR7Agu8ei2JXvIEFd.nksxGvcJ8TA', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`chatId`),
  ADD KEY `userId1` (`userId1`),
  ADD KEY `userId2` (`userId2`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`imageId`),
  ADD KEY `propertyId` (`propertyId`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`newsId`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `passresets`
--
ALTER TABLE `passresets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userId` (`userId`),
  ADD UNIQUE KEY `secretToken` (`secretToken`);

--
-- Indexes for table `property`
--
ALTER TABLE `property`
  ADD PRIMARY KEY (`propertyId`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `chatId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `imageId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `newsId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `passresets`
--
ALTER TABLE `passresets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `property`
--
ALTER TABLE `property`
  MODIFY `propertyId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`userId1`) REFERENCES `users` (`userId`),
  ADD CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`userId2`) REFERENCES `users` (`userId`);

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`);

--
-- Constraints for table `passresets`
--
ALTER TABLE `passresets`
  ADD CONSTRAINT `passresets_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`);

--
-- Constraints for table `property`
--
ALTER TABLE `property`
  ADD CONSTRAINT `userId_fk` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
