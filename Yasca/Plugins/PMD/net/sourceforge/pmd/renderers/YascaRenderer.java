// Decompiled by Jad v1.5.8f. Copyright 2001 Pavel Kouznetsov.
// Jad home page: http://www.kpdus.com/jad.html
// Decompiler options: packimports(3) 
// Source File Name:   YascaRenderer.java

package net.sourceforge.pmd.renderers;

import java.io.IOException;
import java.io.Writer;
import java.text.SimpleDateFormat;
import java.util.*;
import net.sourceforge.pmd.*;
import net.sourceforge.pmd.util.StringUtil;

// Referenced classes of package net.sourceforge.pmd.renderers:
//            OnTheFlyRenderer

public class YascaRenderer extends OnTheFlyRenderer
{

    public YascaRenderer()
    {
        encoding = "UTF-8";
    }

    public void start()
        throws IOException
    {
        Writer writer = getWriter();
        StringBuffer buf = new StringBuffer();
        buf.append((new StringBuilder("<?xml version=\"1.0\" encoding=\"")).append(encoding).append("\"?>").toString()).append(PMD.EOL);
        createVersionAttr(buf);
        createTimestampAttr(buf);
        buf.append('>').append(PMD.EOL);
        writer.write(buf.toString());
    }

    public void renderFileViolations(Iterator violations)
        throws IOException
    {
        Writer writer = getWriter();
        StringBuffer buf = new StringBuffer();
        String filename = null;
        for(; violations.hasNext(); writer.write(buf.toString()))
        {
            buf.setLength(0);
            IRuleViolation rv = (IRuleViolation)violations.next();
            if(!rv.getFilename().equals(filename))
            {
                if(filename != null)
                    buf.append("</file>").append(PMD.EOL);
                filename = rv.getFilename();
                buf.append("<file name=\"");
                StringUtil.appendXmlEscaped(buf, filename);
                buf.append("\">").append(PMD.EOL);
            }
            buf.append("<violation beginline=\"").append(rv.getBeginLine());
            buf.append("\" endline=\"").append(rv.getEndLine());
            buf.append("\" begincolumn=\"").append(rv.getBeginColumn());
            buf.append("\" endcolumn=\"").append(rv.getEndColumn());
            buf.append("\" rule=\"");
            StringUtil.appendXmlEscaped(buf, rv.getRule().getName());
            buf.append("\" ruleset=\"");
            StringUtil.appendXmlEscaped(buf, rv.getRule().getRuleSetName());
            buf.append('"');
            maybeAdd("package", rv.getPackageName(), buf);
            maybeAdd("class", rv.getClassName(), buf);
            maybeAdd("method", rv.getMethodName(), buf);
            maybeAdd("variable", rv.getVariableName(), buf);
            maybeAdd("externalInfoUrl", rv.getRule().getExternalInfoUrl(), buf);
            buf.append(" priority=\"");
            buf.append(rv.getRule().getPriority());
            buf.append("\">").append(PMD.EOL);
            buf.append("<message>");
            StringUtil.appendXmlEscaped(buf, rv.getDescription());
            buf.append("</message>");
            buf.append("<description>");
            StringUtil.appendXmlEscaped(buf, rv.getRule().getDescription());
            buf.append("</description>");
            buf.append("<examples>");
            for(Iterator i = rv.getRule().getExamples().iterator(); i.hasNext(); buf.append("</example>"))
            {
                buf.append("<example>");
                StringUtil.appendXmlEscaped(buf, (String)i.next());
            }

            buf.append("</examples>");
            buf.append(PMD.EOL);
            buf.append("</violation>");
            buf.append(PMD.EOL);
        }

        if(filename != null)
        {
            writer.write("</file>");
            writer.write(PMD.EOL);
        }
    }

    public void end()
        throws IOException
    {
        Writer writer = getWriter();
        StringBuffer buf = new StringBuffer();
        for(Iterator iterator = errors.iterator(); iterator.hasNext(); writer.write(buf.toString()))
        {
            net.sourceforge.pmd.Report.ProcessingError pe = (net.sourceforge.pmd.Report.ProcessingError)iterator.next();
            buf.setLength(0);
            buf.append("<error ").append("filename=\"");
            StringUtil.appendXmlEscaped(buf, pe.getFile());
            buf.append("\" msg=\"");
            StringUtil.appendXmlEscaped(buf, pe.getMsg());
            buf.append("\"/>").append(PMD.EOL);
        }

        if(showSuppressedViolations)
        {
            for(Iterator iterator1 = suppressed.iterator(); iterator1.hasNext(); writer.write(buf.toString()))
            {
                net.sourceforge.pmd.Report.SuppressedViolation s = (net.sourceforge.pmd.Report.SuppressedViolation)iterator1.next();
                buf.setLength(0);
                buf.append("<suppressedviolation ").append("filename=\"");
                StringUtil.appendXmlEscaped(buf, s.getRuleViolation().getFilename());
                buf.append("\" suppressiontype=\"");
                StringUtil.appendXmlEscaped(buf, s.suppressedByNOPMD() ? "nopmd" : "annotation");
                buf.append("\" msg=\"");
                StringUtil.appendXmlEscaped(buf, s.getRuleViolation().getDescription());
                buf.append("\" usermsg=\"");
                StringUtil.appendXmlEscaped(buf, s.getUserMessage() != null ? s.getUserMessage() : "");
                buf.append("\"/>").append(PMD.EOL);
            }

        }
        writer.write("</pmd>");
    }

    private void maybeAdd(String attr, String value, StringBuffer buf)
    {
        if(value != null && value.length() > 0)
        {
            buf.append(' ').append(attr).append("=\"");
            StringUtil.appendXmlEscaped(buf, value);
            buf.append('"');
        }
    }

    private void createVersionAttr(StringBuffer buffer)
    {
        buffer.append("<pmd version=\"").append("4.1").append('"');
    }

    private void createTimestampAttr(StringBuffer buffer)
    {
        buffer.append(" timestamp=\"").append((new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss.SSS")).format(new Date())).append('"');
    }

    private String createTimeElapsedAttr(Report rpt)
    {
        net.sourceforge.pmd.Report.ReadableDuration d = new net.sourceforge.pmd.Report.ReadableDuration(rpt.getElapsedTimeInMillis());
        return (new StringBuilder(" elapsedTime=\"")).append(d.getTime()).append("\"").toString();
    }

    protected String encoding;
}
