<?php

/*
 * This file is part of the LongitudeOne Spatial project.
 *
 * Copyright (c) 2026 Alexandre Tranchant <alexandre.tranchant@gmail.com>
 *
 * See LICENSE for copyright and license information.
 *
 */

declare(strict_types=1);

namespace LongitudeOne\SpatialEncoder\Strategy;

use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialEncoder\Strategy\Ewkb\EwkbTypeEncoder;
use LongitudeOne\SpatialEncoder\Strategy\Wkb\ByteOrderConfigurationTrait;
use LongitudeOne\SpatialTypes\Interfaces\CircularStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\CollectionInterface;
use LongitudeOne\SpatialTypes\Interfaces\CompoundCurveInterface;
use LongitudeOne\SpatialTypes\Interfaces\LineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiLineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\PointInterface;
use LongitudeOne\SpatialTypes\Interfaces\PolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\PolyhedralSurfaceInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Extended Well-Known Binary adapter for XY, XYZ, XYM and XYZM geometries.
 *
 * This class is responsible for encoding a spatial interface to its well-known binary representation.
 */
class EwkbBinaryStrategy extends AbstractConfigurableStrategy implements StrategyInterface
{
    use ByteOrderConfigurationTrait;

    /**
     * Encode a spatial interface to its well-known binary representation.
     *
     * Well-Known binary representation is a standard binary format for representing simple and complex geometries,
     * defined by the Open Geospatial Consortium (OGC).
     *
     * @see https://libgeos.org/specifications/wkb/#extended-wkb
     *
     * @param SpatialInterface $spatial the spatial interface to encode into EWKB format
     *
     * @return string a binary string representing the spatial interface in EWKB format
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws UnsupportedSpatialTypeException      when no implementation exists for the spatial type
     */
    public function encode(SpatialInterface $spatial): string
    {
        return $this->writeGeometry($spatial, true);
    }

    /**
     * Encode each collection member with its own WKB header and dimension.
     *
     * @param CollectionInterface $collection the collection to encode
     *
     * @return string a binary string representing the collection members
     *
     * @throws UnsupportedSpatialInterfaceException when a collection member interface is not supported
     * @throws UnsupportedSpatialTypeException      when no implementation exists for a collection member type
     */
    private function writeCollection(CollectionInterface $collection): string
    {
        $wkb = $this->writeUnsignedInteger(\count($collection->getElements()));

        foreach ($collection->getElements() as $element) {
            $wkb .= $this->writeGeometry($element);
        }

        return $wkb;
    }

    /**
     * Write ordered curve components with their complete binary headers.
     *
     * @param CompoundCurveInterface $compoundCurve the compound curve to write
     *
     * @throws UnsupportedSpatialInterfaceException when a component interface is not supported
     * @throws UnsupportedSpatialTypeException      when a component type is not supported
     */
    private function writeCompoundCurve(CompoundCurveInterface $compoundCurve): string
    {
        $wkb = $this->writeUnsignedInteger(\count($compoundCurve->getCurves()));
        foreach ($compoundCurve->getCurves() as $curve) {
            $wkb .= $this->writeGeometry($curve);
        }

        return $wkb;
    }

    /**
     * Write the coordinates.
     *
     * @param SpatialInterface $spatial the spatial interface to write
     *
     * @return string a binary string representing the coordinates
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws UnsupportedSpatialTypeException      when no implementation exists for the spatial type
     */
    private function writeCoordinates(SpatialInterface $spatial): string
    {
        return match (true) {
            $spatial instanceof PointInterface => $this->writePoint($spatial),
            $spatial instanceof LineStringInterface,
            $spatial instanceof CircularStringInterface => $this->writePointSequence($spatial),
            $spatial instanceof CompoundCurveInterface => $this->writeCompoundCurve($spatial),
            $spatial instanceof PolygonInterface => $this->writePolygon($spatial),
            $spatial instanceof MultiLineStringInterface => $this->writeMultiLineString($spatial),
            $spatial instanceof MultiPolygonInterface => $this->writeMultiPolygon($spatial),
            $spatial instanceof CollectionInterface => $this->writeCollection($spatial),
            $spatial instanceof PolyhedralSurfaceInterface => $this->writePolyhedralSurface($spatial),

            default => throw new UnsupportedSpatialInterfaceException($spatial::class),
        };
    }

    /**
     * Write a complete geometry, including the SRID only at the root.
     *
     * @param SpatialInterface $spatial     the geometry to encode
     * @param bool             $includeSrid whether to include its spatial reference
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws UnsupportedSpatialTypeException      when no implementation exists for the spatial type
     */
    private function writeGeometry(SpatialInterface $spatial, bool $includeSrid = false): string
    {
        $includeSrid = $includeSrid && 0 !== $spatial->getSrid();
        $wkb = $this->writeByteOrder();
        $wkb .= new EwkbTypeEncoder()->writeType($spatial, $includeSrid, $this->byteOrder());
        if ($includeSrid) {
            $wkb .= $this->writeUnsignedInteger($spatial->getSrid());
        }
        $wkb .= $this->writeCoordinates($spatial);

        return $wkb;
    }

    /**
     * Write a multi-line string.
     *
     * @param MultiLineStringInterface $multiLineString the multi-line string to write
     *
     * @return string a binary string representing the multi-line string
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws UnsupportedSpatialTypeException      when no implementation exists for the spatial type
     */
    private function writeMultiLineString(MultiLineStringInterface $multiLineString): string
    {
        $wkb = $this->writeUnsignedInteger(\count($multiLineString->getLineStrings()));
        foreach ($multiLineString->getLineStrings() as $lineString) {
            $wkb .= $this->writeGeometry($lineString);
        }

        return $wkb;
    }

    /**
     * Write a multipolygon.
     *
     * @param MultiPolygonInterface $multiPolygon the multipolygon to write
     *
     * @return string a binary string representing the multipolygon
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws UnsupportedSpatialTypeException      when no implementation exists for the spatial type
     */
    private function writeMultiPolygon(MultiPolygonInterface $multiPolygon): string
    {
        $wkb = $this->writeUnsignedInteger(\count($multiPolygon->getPolygons()));
        foreach ($multiPolygon->getPolygons() as $polygon) {
            $wkb .= $this->writeGeometry($polygon);
        }

        return $wkb;
    }

    /**
     * Write a point.
     *
     * @param PointInterface $point the point to write
     *
     * @return string a binary string representing the point
     */
    private function writePoint(PointInterface $point): string
    {
        if ([] === $point->toArray()) {
            $dimension = 2 + ($point->hasZ() ? 1 : 0) + ($point->hasM() ? 1 : 0);

            // Canonical IEEE-754 quiet NaN in the selected byte order.
            return $this->writeEmptyPoint($dimension);
        }

        return pack($this->doubleFormat(), ...$point->toArray());
    }

    /**
     * Write the ordered points of a linear or circular string without interpolation.
     *
     * @param CircularStringInterface|LineStringInterface $lineString the point sequence to write
     *
     * @return string a binary string containing the point count and coordinates
     */
    private function writePointSequence(CircularStringInterface|LineStringInterface $lineString): string
    {
        $wkb = $this->writeUnsignedInteger(\count($lineString->getPoints()));
        foreach ($lineString->getPoints() as $point) {
            $wkb .= $this->writePoint($point);
        }

        return $wkb;
    }

    /**
     * Write a polygon.
     *
     * @param PolygonInterface $polygon the polygon to write
     *
     * @return string a binary string representing the polygon
     */
    private function writePolygon(PolygonInterface $polygon): string
    {
        $wkb = $this->writeUnsignedInteger(\count($polygon->getRings()));

        foreach ($polygon->getRings() as $ring) {
            $wkb .= $this->writePointSequence($ring);
        }

        return $wkb;
    }

    /**
     * Write a polyhedral surface as a count followed by complete polygon WKBs.
     *
     * @param PolyhedralSurfaceInterface $surface the surface to write
     *
     * @throws UnsupportedSpatialInterfaceException when a patch interface is not supported
     * @throws UnsupportedSpatialTypeException      when no implementation exists for a patch type
     */
    private function writePolyhedralSurface(PolyhedralSurfaceInterface $surface): string
    {
        $wkb = $this->writeUnsignedInteger(\count($surface->getPatches()));
        foreach ($surface->getPatches() as $patch) {
            $wkb .= $this->writeGeometry($patch);
        }

        return $wkb;
    }
}
