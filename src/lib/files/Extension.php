<?php
/*
 * Synopsy PHP Framework (c) by Webdesign Studio s.r.o.
 * 
 * Synopsy PHP Framework is licensed under a
 * Creative Commons Attribution 4.0 International License.
 *
 * You should have received a copy of the license along with this
 * work. If not, see <http://creativecommons.org/licenses/by/4.0/>.
 *
 * Any files in this application that are NOT marked with this disclaimer are
 * not part of the framework's open-source implementation, the CC 4.0 licence
 * does not apply to them and are protected by standard copyright laws!
 */

namespace Synopsy\Files;

/**
 * Miniclass holding Extension constants.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Extension {
    
    // Image
    const JPG = 'jpg';
    const JPEG = 'jpeg';
    const PNG = 'png';
    const GIF = 'gif';
    const BMP = 'bmp';
    const PS = 'ps';
    const SVG = 'svg';
    
    // Text
    const PDF = 'pdf';
    const TXT = 'txt';
    const LOG = 'log';
    const RTF = 'rtf';
    const XML = 'xml';
    
    // Archives
    const ZIP = 'zip';
    const RAR = 'rar';
    const TAR = 'tar';
    
    // MS Office
    const DOC = 'doc';
    const DOCX = 'docx';
    const XLS = 'xls';
    const XLSX = 'xlsx';
    const PPS = 'pps';
    const PPSX = 'ppsx';
    const PPT = 'ppt';
    const PPTX = 'pptx';
    
    // Open office
    const ODT = 'odt';
    const ODS = 'ods';
    const ODP = 'odp';
    
    public static $extensions = [
        
        self::JPG,
        self::JPEG,
        self::PNG,
        self::GIF,
        self::BMP,
        self::PS,
        self::SVG,
        
        self::PDF,
        self::TXT,
        self::LOG,
        self::RTF,
        self::XML,
        
        self::ZIP,
        self::RAR,
        self::TAR,
        
        self::DOC,
        self::DOCX,
        self::XLS,
        self::XLSX,
        self::PPS,
        self::PPSX,
        self::PPT,
        self::PPTX,
        
        self::ODT,
        self::ODS,
        self::ODP
            
    ];
    
}
