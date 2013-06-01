<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace ZenMagick\StorefrontBundle\Controller;

use ZenMagick\Base\Toolbox;
use ZenMagick\ZenMagickBundle\Controller\DefaultController;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Download controller.
 *
 */
class DownloadController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processGet($request)
    {
        $translator = $this->get('translator');
        $orderId = $request->query->get('order');
        $id = $request->query->get('id');

        if (null == $orderId || null == $id) {
            $this->get('session.flash_bag')->error($translator->trans('Download not found'));

            return $this->findView('error');
        }

        $languageId = $request->getSession()->getLanguageId();
        $order = $this->container->get('orderService')->getOrderForId($orderId, $languageId);
        $account = $this->getUser();
        if ($account->getId() != $order->getAccountId()) {
            $this->get('session.flash_bag')->error($translator->trans('Order not found'));

            return $this->findView('error');
        }

        $product = null;
        foreach ($order->getDownloads() as $download) {
            if ($download->getId() == $id) $product = $download;
        }

        if (null == $product || !$product->isDownloadable()) {
            $this->get('session.flash_bag')->error($translator->trans('No such download or download has expired.'));

            return $this->findView('error');
        }
        if ($product->getMaxDays() > 0) { // ignore for unlimited downloads
            $query = "UPDATE %table.orders_products_download% SET download_count = download_count - 1
                WHERE orders_products_download_id = :id";
            \ZMRuntime::getDatabase()->updateObj($query, array('id' => $id), 'orders_products_download');
        }

        $settingsService = $this->container->get('settingsService');
        $downloadBaseDir = $settingsService->get('downloadBaseDir');
        $fileName = $product->getFilename();
        $filePath = $downloadBaseDir.'/'.$fileName;
        $fileSize = $product->getFilesize();
        $outputFileName = basename(str_replace(' ', '_', $fileName));

        // Download by redirect.
        // @todo only works on windows >= Vista. Should have a warning somewhere.
        if ($settingsService->get('downloadByRedirect')) {
            // @todo use web accessible cache sub directory for downloadPubDir
            $pubDir = $settingsService->get('downloadPubDir');
            if (empty($pubDir) || !is_writeable($pubDir)) {
                $this->get('session.flash_bag')->error($translator->trans('Could not write to public download directory.'));

                return $this->findView('error');
            }

            /**
             *  @todo this seems like an obvious race condition when more than one download is happening.
             *  But i have heard no reports of it causing problems. INVESTIGATE!!
             */
            $this->cleanTempDir($pubDir);
            $pubLocalDir = '.'.Toolbox::random(32);
            umask(0000);
            mkdir($pubDir.'/'.$pubLocalDir, 0777, true);
            $target = $pubDir.'/'.$pubLocalDir.'/'.$outputFileName;
            $link = @symlink($filePath, $target);
            if ($link) {
                $url = $this->getRequest()->getUriForPath($target);
                return new RedirectRedirect($url, 303);
            }
        }
        // Streaming downloads.

        // @todo offer a generic streaming method on the controller
        // and rely on HttpFoundation\Response

        if (headers_sent()) {
            $msg = 'Could not send download because headers were already sent.';
            throw new ZMException($msg);
        }

        ini_set('zlib.output_compression', 'Off');
        /**
         * Now send the file with header() magic
         * The "must-revalidate" and expiry times are used to prevent caching and fraudulent re-acquiring of files w/o redownloading.
         * Certain browsers require certain header combinations, especially when related to SSL mode and caching
         *
         * @todo rely on HttpFoundation\Response
         * @copyright the zencart developers
         */
        header('Expires: Mon, 22 Jan 2002 00:00:00 GMT');
        header('Last-Modified: '.gmdate('D,d M Y H:i:s').' GMT');
        if (preg_match('/msie/i', $request->server->get('HTTP_USER_AGENT'))) {
            header('Pragma: public');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', FALSE);
            header('Cache-Control: max-age=1');  // stores for only 1 second, which helps allow SSL downloads to work more reliably in IE
        } else {
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
        }
        // force file to be downloaded.
        header('Content-Type: application/x-octet-stream');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="'.urlencode($outputFileName).'"');
        if ($fileSize > 0) header('Content-Length: '.(string) $fileSize);

        if (!$settingsService->get('downloadInChunks')) {
            readfile($filePath);
        } else {
            @set_time_limit(1500);
            $fp = fopen($filePath, 'rb');
            while (!feof($fp)) {
                echo fread($fp, 4096);
                flush();
            }
            fclose($fp);

            return null;
        }
    }

    /**
     * Delete all directories and symlinked files (and only symlinked files)
     */
    protected function cleanTempDir($dir)
    {
        if (empty($dir) || !is_writable($dir)) return;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
             \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $path) {
            if ($path->isDir()) {
                rmdir($path->getPathname());
            } elseif ($path->isLink()) {
                unlink($path->getPathname());
            }
        }
    }
}
