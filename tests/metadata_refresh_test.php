<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Testcase class for metadata_refresh task class.
 *
 * @package    auth_saml2
 * @author     Sam Chaffee
 * @copyright  Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use auth_saml2\config;
use auth_saml2\task\metadata_refresh;

defined('MOODLE_INTERNAL') || die();

/**
 * Testcase class for metadata_refresh task class.
 *
 * @package    auth_saml2
 * @copyright  Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_saml2_metadata_refresh_testcase extends basic_testcase {

    public function test_metadata_refresh_idpmetadata_non_url() {
        $config = new config(false);
        $config->idpmetadata = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<somexml>yada</somexml>
XML;

        $refreshtask = new metadata_refresh();
        $refreshtask->set_config($config);

        $this->expectOutputString('IDP metadata config not a URL, nothing to refresh.' . "\n");
        $refreshtask->execute();
    }

    public function test_metadata_refresh_fetch_fails() {
        $config = new config(false);
        $config->idpmetadata = 'http://somefakeidpurl.local';
        $fetcher = $this->prophesize('auth_saml2\metadata_fetcher');

        $refreshtask = new metadata_refresh();
        $refreshtask->set_fetcher($fetcher->reveal());
        $refreshtask->set_config($config);

        $fetcher->fetch($config->idpmetadata)->willThrow(new \coding_exception('Metadata fetch failed: some error'));

        $this->expectOutputString('Metadata fetch failed.' . "\n");
        $refreshtask->execute();
    }

    public function test_metadata_refresh_parse_fails() {
        $config = new config(false);
        $config->idpmetadata = 'https://somefakeidpurl.local';
        $fetcher = $this->prophesize('auth_saml2\metadata_fetcher');
        $parser = $this->prophesize('auth_saml2\metadata_parser');

        $refreshtask = new metadata_refresh();
        $refreshtask->set_config($config);
        $refreshtask->set_fetcher($fetcher->reveal());
        $refreshtask->set_parser($parser->reveal());

        $fetcher->fetch($config->idpmetadata)->willReturn('doesnotmatter');
        $parser->parse('doesnotmatter')->willThrow(new \coding_exception('Metadata parse failed: some error'));

        $this->expectOutputString('Metadata parsing failed.' . "\n");
        $refreshtask->execute();
    }

    public function test_metadata_refresh_write_fails() {
        $config = new config(false);
        $config->idpmetadata = 'https://somefakeidpurl.local';
        $fetcher = $this->prophesize('auth_saml2\metadata_fetcher');
        $parser = $this->prophesize('auth_saml2\metadata_parser');
        $writer = $this->prophesize('auth_saml2\metadata_writer');

        $refreshtask = new metadata_refresh();
        $refreshtask->set_config($config);
        $refreshtask->set_fetcher($fetcher->reveal());
        $refreshtask->set_parser($parser->reveal());
        $refreshtask->set_writer($writer->reveal());

        $fetcher->fetch($config->idpmetadata)->willReturn('somexml');
        $parser->parse('somexml')->willReturn(null);
        $writer->write('idp.xml', 'somexml')->willThrow(new coding_exception('Metadata write failed: some error'));

        $this->expectOutputString('Metadata write failed.' . "\n");
        $refreshtask->execute();
    }
}